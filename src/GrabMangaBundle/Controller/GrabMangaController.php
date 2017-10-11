<?php

namespace GrabMangaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class GrabMangaController extends Controller
{
    public function setContainer(ContainerInterface $container = null) {
        try {
            $this->container = $container;
            $request = $container->get('request_stack')->getCurrentRequest();
            if (!$request->attributes->has('token')) {
                throw new \Exception("Token non fourni", Response::HTTP_UNAUTHORIZED);
            }
            $token = $request->attributes->get('token');
            $newToken = $this->get('security.service')->checkAndUpdateToken($token);
            $request->attributes->set('token', $newToken);
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
            $request = $this->get('request_stack')->getCurrentRequest();
            return [
                'token' => $request->attributes->get('token'),
                'data' => $data,
            ];
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de génération de la réponse : ".$ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
