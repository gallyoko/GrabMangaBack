<?php

namespace GrabMangaBundle\Services;

use GrabMangaBundle\Entity\Manga;
use GrabMangaBundle\Entity\MangaTome;
use GrabMangaBundle\Generic\Book;
use GrabMangaBundle\Generic\BookTome;
use Symfony\Component\HttpFoundation\Response;

class MangaTomeService {
	private $doctrine;
	private $validator;
	private $serviceMessage;
    private $serviceMangaChapter;
	
	public function __construct($doctrine, $validator, $serviceMessage, $serviceMangaChapter) {
		$this->doctrine = $doctrine;
		$this->validator = $validator;
		$this->serviceMessage = $serviceMessage;
        $this->serviceMangaChapter = $serviceMangaChapter;
	}

    public function getOne($id) {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:MangaTome');
            $mangaTome = $repo->find($id);
            if(!$mangaTome) {
                throw new \Exception("Aucun Tome.", Response::HTTP_NOT_FOUND);
            }
            return $mangaTome;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur du tome : ". $ex->getMessage(), $ex->getCode());
        }
    }

    public function add(Manga $manga, Book $book) {
        try {
            $em = $this->doctrine->getManager();
            $bookTomes = $book->getBookTomes();
            foreach ($bookTomes as $bookTome) {
                $mangaTome = null;
                if ($bookTome->getTitle()) {
                    $mangaTome = $this->getOneByTitle($manga, $bookTome);
                    if (!$mangaTome) {
                        $mangaTome = new MangaTome();
                        $mangaTome->setManga($manga)
                            ->setTitle($bookTome->getTitle());
                        $errors = $this->validator->validate($mangaTome);
                        if (count($errors)>0) {
                            throw new \Exception($this->serviceMessage->formatErreurs($errors));
                        }
                        $em->persist($mangaTome);
                        $em->flush();
                    }
                }
                $this->serviceMangaChapter->add($manga, $bookTome, $mangaTome);
            }
        } catch (\Exception $ex) {
            throw new \Exception("Erreur d'enregistrement d'un tome du manga : ". $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getByManga(Manga $manga) {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:MangaTome');
            $mangaTomes = $repo->findBy([
                "manga" => $manga,
            ]);
            $data = [];
            foreach ($mangaTomes as $mangaTome) {
                $data[] = [
                    "id" => $mangaTome->getId(),
                    "title" => $mangaTome->getTitle(),
                ];
            }
            return $data;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération des tomes du manga : ". $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getOneByTitle(Manga $manga, BookTome $bookTome) {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:MangaTome');
            $mangaTome = $repo->findOneBy([
                "manga" => $manga,
                "title" => $bookTome->getTitle(),
            ]);
            return $mangaTome;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors du controle d'existence du tome du manga : ". $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}