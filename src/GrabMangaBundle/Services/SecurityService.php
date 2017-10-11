<?php

namespace GrabMangaBundle\Services;

use GrabMangaBundle\Entity\TokenUser;
use Symfony\Component\HttpFoundation\Response;
use GrabMangaBundle\Entity\User;

class SecurityService {

    private $doctrine;
    private $em;
    private $validator;
    private $serviceMessage;

    const TIME_ELAPSED = 60*15;
    const KEEP_MAX_TOKEN = 10;

    public function __construct($doctrine, $validator, $serviceMessage) {
        $this->doctrine = $doctrine;
        $this->em = $doctrine->getManager();
        $this->validator = $validator;
        $this->serviceMessage = $serviceMessage;
    }

    public function auth($json) {
        try {
            $data = json_decode($json);
            if (!isset($data->login) && !isset($data->password)) {
                throw new \Exception("Veuillez fournir un login et mot de passe.", Response::HTTP_UNAUTHORIZED);
            }
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:User');
            $user = $repo->findOneBy([
                'login' => $data->login,
                'password' => md5($data->password),
            ]);
            if(!$user) {
                throw new \Exception("Utilisateur inconnu.", Response::HTTP_UNAUTHORIZED);
            }
            $token = $this->generateAndUpdateToken($user);
            $profil = $user->getProfil();
            return [
                'token' => $token,
                'profil' => $profil,
            ];
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function generateAndUpdateToken(User $user) {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:TokenUser');
            $tokensUser = $repo->findBy([
                'user' => $user,
            ]);
            foreach ($tokensUser as $tokenUser) {
                $this->em->remove($tokenUser);
            }
            $this->em->flush();
            $token = bin2hex(openssl_random_pseudo_bytes(16));
            $tokenUser = new TokenUser();
            $tokenUser->setUser($user)
                ->setValue($token)
                ->setTime(time());
            $errors = $this->validator->validate($tokenUser);
            if (count($errors)>0) {
                throw new \Exception($this->serviceMessage->formatErreurs($errors), 500);
            }
            $this->em->persist($tokenUser);
            $this->em->flush();
            return $token;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors de la génération et mise à jour du token utilisateur : ".$ex->getMessage(), $ex->getCode());
        }
    }

    public function checkAndUpdateToken($token) {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:TokenUser');
            $oldTime = time() - self::TIME_ELAPSED;
            $tokenUsersToDelete = $repo->getTokenUserToDelete($oldTime);
            foreach ($tokenUsersToDelete as $tokenUserToDelete) {
                $this->em->remove($tokenUserToDelete);
            }
            $this->em->flush();

            $tokenUser = $repo->findOneBy([
                'value' => $token,
            ]);
            if (!$tokenUser) {
                throw new \Exception("Erreur lors du contrôle d'autentification.", 404);
            }
            $user = $tokenUser->getUser();
            $checkTokenUsersToDelete = $repo->findBy([
                'user' => $user,
            ], ['id' => 'ASC']);
            if (count($checkTokenUsersToDelete) >= self::KEEP_MAX_TOKEN) {
                $tokenUsersToDelete = array_slice($checkTokenUsersToDelete, 0, self::KEEP_MAX_TOKEN);
                foreach ($tokenUsersToDelete as $tokenUserToDelete) {
                    $this->em->remove($tokenUserToDelete);
                }
                $this->em->flush();
            }

            $newToken = bin2hex(openssl_random_pseudo_bytes(16));
            $newTokenUser = new TokenUser();
            $newTokenUser->setUser($user)
                ->setValue($newToken)
                ->setTime(time());
            $errors = $this->validator->validate($newTokenUser);
            if (count($errors)>0) {
                throw new \Exception($this->serviceMessage->formatErreurs($errors), 500);
            }
            $this->em->persist($newTokenUser);
            $this->em->flush();

            return $newToken;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors du contrôle et la mise à jour du token utilisateur : ".$ex->getMessage(), $ex->getCode());
        }
    }

    public function getUser($token) {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:TokenUser');
            $tokenUser = $repo->findOneBy([
                'value' => $token,
            ]);
            if(!$tokenUser) {
                throw new \Exception("Erreur lors du contrôle d'autentification.", Response::HTTP_UNAUTHORIZED);
            }
            return $tokenUser->getUser();
        } catch (\Exception $ex) {
            throw new \Exception("Impossible de récupérer l'utilisateur : ". $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}