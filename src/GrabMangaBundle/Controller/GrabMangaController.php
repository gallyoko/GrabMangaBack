<?php

namespace GrabMangaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class GrabMangaController extends Controller
{
    private $token = null;

    public function setContainer(ContainerInterface $container = null) {
        try {
            $this->container = $container;
            $request = $container->get('request_stack')->getCurrentRequest();
            if (!$request->attributes->has('token')) {
                throw new \Exception("Token non fourni", Response::HTTP_UNAUTHORIZED);
            }
            $token = $request->attributes->get('token');
            $this->token = $this->get('security.service')->checkAndUpdateToken($token);
        } catch (\Exception $ex) {
            throw new \Exception("Alerte sécurité : ".$ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @param $data
     * @return array
     * @throws \Exception
     */
    public function setResponse($data) {
        try {
            return [
                'token' => $this->token,
                'data' => $data,
            ];
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de génération de la réponse : ".$ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @return User
     * @throws \Exception
     */
    public function getUser(){
        try {
            if (!$this->token) {
                throw new \Exception("Aucun token défini", Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            return $this->get('security.service')->getUser($this->token);
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération de l'utilisateur courant : ".$ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
