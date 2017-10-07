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
            $oldTime = time() - (60 * 15);
            $queryDelete = "DELETE FROM token_user WHERE time<'" . $oldTime . "';";
            $resultDelete = $this->db->exec($queryDelete);
            $querySelect = "SELECT * FROM token_user WHERE value='" . $token . "';";
            $resultSelect = $this->db->fetchAll($querySelect);
            if (count($resultSelect) == 1) {
                $userId = $resultSelect[0]["user_id"];
                $querySelect = "SELECT * FROM token_user WHERE user_id='" . $userId .
                    "' ORDER BY id ASC;";
                $resultSelect = $this->db->fetchAll($querySelect);
                $countResultSelect = count($resultSelect);
                if (count($resultSelect) > 9) {
                    $queryDelete = "DELETE FROM token_user WHERE id='" . $resultSelect[0]["id"] .
                        "';";
                    $resultDelete = $this->db->exec($queryDelete);
                }
                $token = bin2hex(openssl_random_pseudo_bytes(16));
                $queryInsert = "INSERT INTO token_user (user_id, value, time) VALUES ('" . $userId .
                    "', '" . $token . "', '" . time() . "');";
                $result = $this->db->exec($queryInsert);
                if (! $result) {
                    throw new \Exception("Erreur lors de l'injection du token de l'utilisateur.");
                }
            } else {
                throw new \Exception("Erreur lors du contrôle d'autentification.");
            }
            return $token;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getUser($token) {
        try {
            $querySelect = "SELECT * FROM token_user WHERE value='" . $token . "';";
            $resultSelect = $this->db->fetchAll($querySelect);
            if (count($resultSelect) == 1) {
                $userId = $resultSelect[0]['user_id'];
            } else {
                throw new \Exception("Erreur lors du contrôle d'autentification.");
            }
            return $userId;
        } catch (\Exception $ex) {
            return $ex;
        }
    }
	
}