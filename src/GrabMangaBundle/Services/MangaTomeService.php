<?php

namespace GrabMangaBundle\Services;

use GrabMangaBundle\Entity\Manga;
use GrabMangaBundle\Entity\MangaTome;
use GrabMangaBundle\Generic\BookTome;
use Symfony\Component\HttpFoundation\Response;

class MangaTomeService {
	private $doctrine;
	private $validator;
	private $serviceMessage;
    private $serviceMangaChapter;

    /**
     * MangaTomeService constructor.
     *
     * @param $doctrine
     * @param $validator
     * @param MessageService $serviceMessage
     * @param MangaChapterService $serviceMangaChapter
     */
	public function __construct($doctrine, $validator, MessageService $serviceMessage,
                                MangaChapterService $serviceMangaChapter) {
		$this->doctrine = $doctrine;
		$this->validator = $validator;
		$this->serviceMessage = $serviceMessage;
        $this->serviceMangaChapter = $serviceMangaChapter;
	}

    /**
     * @param $id
     * @return mixed
     * @throws \Exception
     */
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

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getList() {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:MangaTome');
            $mangaTome = $repo->findAll();
            if(!$mangaTome) {
                throw new \Exception("Aucun Tome.", Response::HTTP_NOT_FOUND);
            }
            return $mangaTome;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération des tomes : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @param Manga $manga
     * @param $bookTomes
     * @throws \Exception
     */
    public function add(Manga $manga, $bookTomes) {
        try {
            $em = $this->doctrine->getManager();
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
                            throw new \Exception($this->serviceMessage->formatErreurs($errors), 500);
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

    /**
     * @param Manga $manga
     * @param BookTome $bookTome
     * @return mixed
     * @throws \Exception
     */
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

    /***************************************************
     ********************* API *************************
     ***************************************************/

    /**
     * @param Manga $manga
     * @param bool $json
     * @return array
     * @throws \Exception
     */
    public function getByManga(Manga $manga, $json = false) {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:MangaTome');
            $mangaTomes = $repo->findBy([
                "manga" => $manga,
            ], ['id' => 'DESC']);
            if ($json) {
                $data = [];
                foreach ($mangaTomes as $mangaTome) {
                    $data[] = [
                        "id" => $mangaTome->getId(),
                        "title" => $mangaTome->getTitle(),
                    ];
                }
            } else {
                $data = $mangaTomes;
            }
            return $data;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération des tomes du manga : ". $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Retourne les informations d'un tome en fonction de son identifiant
     *
     * @param $mangaTomeId
     * @return array
     * @throws \Exception
     */
    public function getInfo($mangaTomeId) {
        try {
            $mangaTome = $this->getOne($mangaTomeId);
            $mangaChapters = $this->serviceMangaChapter->getByTome($mangaTome);
            $info = [
                'id' => $mangaTome->getId(),
                'title' => $mangaTome->getTitle(),
                'countChapter' => count($mangaChapters),
                'manga' => [
                    'id' => $mangaTome->getManga()->getId(),
                    'title' => $mangaTome->getManga()->getTitle(),
                    'synopsis' => $mangaTome->getManga()->getSynopsis(),
                    'cover' => $mangaTome->getManga()->getCover(),
                ],
            ];
            return $info;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors de la récupération des informations du tome du manga : ". $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}