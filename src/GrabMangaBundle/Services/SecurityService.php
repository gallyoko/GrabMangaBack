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
    private $securityParameter;

    /**
     * SecurityService constructor.
     *
     * @param $doctrine
     * @param $validator
     * @param MessageService $serviceMessage
     * @param array $securityParameter
     */
    public function __construct($doctrine, $validator, MessageService $serviceMessage,
                                $securityParameter) {
        $this->doctrine = $doctrine;
        $this->em = $doctrine->getManager();
        $this->validator = $validator;
        $this->serviceMessage = $serviceMessage;
        $this->securityParameter = $securityParameter;
    }

    /**
     * Authentification
     *
     * @param $json
     * @return array
     * @throws \Exception
     */
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
            throw new \Exception("Erreur d'authentification : ".$ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Génère / renvoie un token pour l'utilisateur fourni
     *
     * @param User $user
     * @return string
     * @throws \Exception
     */
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
                throw new \Exception($this->serviceMessage->formatErreurs($errors), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $this->em->persist($tokenUser);
            $this->em->flush();
            return $token;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors de la génération et mise à jour du token utilisateur : ".$ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Contrôle le token fourni et en génère / renvoie un nouveau
     *
     * @param $token
     * @param bool $json
     * @return string
     * @throws \Exception
     */
    public function checkAndUpdateToken($token, $json=false) {
        try {
            if ($json) {
                $data = json_decode($token);
                if (!isset($data->token)) {
                    throw new \Exception("Veuillez fournir un token.", Response::HTTP_UNAUTHORIZED);
                }
                $token = $data->token;
            }
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:TokenUser');
            $oldTime = time() - $this->securityParameter['token']['timeLimit'];
            $tokenUsersToDelete = $repo->getTokenUserToDelete($oldTime);
            foreach ($tokenUsersToDelete as $tokenUserToDelete) {
                $this->em->remove($tokenUserToDelete);
            }
            $this->em->flush();

            $tokenUser = $repo->findOneBy([
                'value' => $token,
            ]);
            if (!$tokenUser) {
                throw new \Exception("Erreur lors du contrôle d'autentification.", Response::HTTP_UNAUTHORIZED);
            }
            $user = $tokenUser->getUser();
            $checkTokenUsersToDelete = $repo->findBy([
                'user' => $user,
            ], ['id' => 'ASC']);
            if (count($checkTokenUsersToDelete) >= $this->securityParameter['token']['countLimit']) {
                $tokenUsersToDelete = array_slice($checkTokenUsersToDelete, 0, $this->securityParameter['token']['countLimit']);
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
                throw new \Exception($this->serviceMessage->formatErreurs($errors), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $this->em->persist($newTokenUser);
            $this->em->flush();

            if ($json) {
                $newToken = [
                    'token' => $newToken,
                ];
            }
            return $newToken;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors du contrôle et la mise à jour du token utilisateur : ".$ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * retourne l'utilisateur correspondant au token fourni
     *
     * @param $token
     * @return User
     * @throws \Exception
     */
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

    /**
     * retourne l'utilisateur correspondant à l'identifiant fourni
     *
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function getUserById($id) {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:User');
            $user = $repo->find($id);
            if(!$user) {
                throw new \Exception("Erreur lors de la récupération de l'utilisateur.", Response::HTTP_UNAUTHORIZED);
            }
            return $user;
        } catch (\Exception $ex) {
            throw new \Exception("Impossible de récupérer l'utilisateur : ". $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}