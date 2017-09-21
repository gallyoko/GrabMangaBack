<?php

namespace GrabMangaBundle\Services;

use GrabMangaBundle\Entity\Manga;
use Symfony\Component\HttpFoundation\Response;

class MangaService {

	private $doctrine;
	private $validator;
	private $serviceMessage;
	
	public function __construct($doctrine, $validator, $serviceMessage) {
		$this->doctrine = $doctrine;
		$this->validator = $validator;
		$this->serviceMessage = $serviceMessage;
	}

    public function getList() {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:Manga');
            $data = $repo->findAll();
            if(count($data) == 0) {
                throw new \Exception("Aucun manga.", Response::HTTP_NOT_FOUND);
            }
            return $data;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération de la liste des manga : ". $ex->getMessage(), $ex->getCode());
        }
    }

}