<?php

namespace GrabMangaBundle\Services;

use GrabMangaBundle\Entity\Manga;
use GrabMangaBundle\Generic\Book;
use Symfony\Component\HttpFoundation\Response;

class MangaService {

	private $doctrine;
	private $validator;
	private $serviceMessage;
    private $serviceMangaTome;

    /**
     * MangaService constructor.
     *
     * @param $doctrine
     * @param $validator
     * @param MessageService $serviceMessage
     * @param MangaTomeService $serviceMangaTome
     */
	public function __construct($doctrine, $validator, MessageService $serviceMessage,
                                MangaTomeService $serviceMangaTome) {
		$this->doctrine = $doctrine;
		$this->validator = $validator;
		$this->serviceMessage = $serviceMessage;
        $this->serviceMangaTome = $serviceMangaTome;
	}

    /**
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function getOne($id) {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:Manga');
            $manga = $repo->find($id);
            if(!$manga) {
                throw new \Exception("Aucun manga.", Response::HTTP_NOT_FOUND);
            }
            return $manga;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération du manga : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @return mixed
     * @throws \Exception
     */
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

    /**
     * @param Book $book
     * @throws \Exception
     */
    public function add(Book $book) {
        try {
            $em = $this->doctrine->getManager();
            $manga = $this->getOneByTitle($book);
            $mangaExist = false;
            if ($manga) {
                $mangaExist = true;
            } else {
                $manga = new Manga();
            }
            $manga->setTitle($book->getTitle())
                ->setUrl($book->getUrl())
                ->setSynopsis($book->getSynopsis())
                ->setCover($book->getCover());
            $errors = $this->validator->validate($manga);
            if (count($errors)>0) {
                throw new \Exception($this->serviceMessage->formatErreurs($errors), 500);
            }
            if ($mangaExist) {
                $em->merge($manga);
            } else {
                $em->persist($manga);
            }
            $em->flush();
        } catch (\Exception $ex) {
            throw new \Exception("Erreur d'enregistrement du titre manga : ". $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Book $book
     * @return mixed
     * @throws \Exception
     */
    private function getOneByTitle(Book $book) {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:Manga');
            $manga = $repo->findOneBy([
                "title" => $book->getTitle(),
            ]);
            return $manga;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors du controle d'existence du titre manga : ". $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}