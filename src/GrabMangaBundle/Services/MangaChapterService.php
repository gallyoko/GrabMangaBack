<?php

namespace GrabMangaBundle\Services;

use GrabMangaBundle\Entity\Manga;
use GrabMangaBundle\Entity\MangaTome;
use GrabMangaBundle\Entity\MangaChapter;
use GrabMangaBundle\Generic\BookChapter;
use GrabMangaBundle\Generic\BookTome;
use Symfony\Component\HttpFoundation\Response;

class MangaChapterService {

	private $doctrine;
	private $validator;
	private $serviceMessage;
    private $serviceMangaEbook;

    /**
     * MangaChapterService constructor.
     *
     * @param $doctrine
     * @param $validator
     * @param MessageService $serviceMessage
     * @param MangaEbookService $serviceMangaEbook
     */
    public function __construct($doctrine, $validator, MessageService $serviceMessage,
                                MangaEbookService $serviceMangaEbook) {
        $this->doctrine = $doctrine;
        $this->validator = $validator;
        $this->serviceMessage = $serviceMessage;
        $this->serviceMangaEbook = $serviceMangaEbook;
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function getOne($id) {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:MangaChapter');
            $mangaChapter = $repo->find($id);
            if(!$mangaChapter) {
                throw new \Exception("Aucun Chapitre.", Response::HTTP_NOT_FOUND);
            }
            return $mangaChapter;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur du chapitre : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getList() {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:MangaChapter');
            $mangaChapters = $repo->findAll();
            if(count($mangaChapters)==0) {
                throw new \Exception("Aucun Chapitre.", Response::HTTP_NOT_FOUND);
            }
            return $mangaChapters;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de recuperation des chapitres : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @param Manga $manga
     * @param BookTome $bookTome
     * @param MangaTome|null $mangaTome
     * @throws \Exception
     */
    public function add(Manga $manga, BookTome $bookTome, MangaTome $mangaTome = null) {
        try {
            $em = $this->doctrine->getManager();
            $bookChapters = $bookTome->getBookChapters();
            foreach ($bookChapters as $bookChapter) {
                $mangaChapter = $this->getOneByTitle($manga, $bookChapter, $mangaTome);
                $mangaChapterExist = false;
                if ($mangaChapter) {
                    $mangaChapterExist = true;
                } else {
                    $mangaChapter = new MangaChapter();
                }
                $mangaChapter->setTitle($bookChapter->getTitle())
                    ->setUrl($bookChapter->getUrl())
                    ->setMangaTome($mangaTome)
                    ->setManga($manga);
                $errors = $this->validator->validate($mangaChapter);
                if (count($errors)>0) {
                    throw new \Exception($this->serviceMessage->formatErreurs($errors), 500);
                }
                if ($mangaChapterExist) {
                    $em->merge($mangaChapter);
                } else {
                    $em->persist($mangaChapter);
                }
            }
            $em->flush();
        } catch (\Exception $ex) {
            throw new \Exception("Erreur d'enregistrement d'un chapitre d'un tome du manga : ". $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Manga $manga
     * @param bool $json
     * @return array
     * @throws \Exception
     */
    public function getByManga(Manga $manga, $json =false) {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:MangaChapter');
            $mangaChapters = $repo->findBy([
                "manga" => $manga,
            ]);
            if ($json) {
                $data = [];
                foreach ($mangaChapters as $mangaChapter) {
                    $data[] = [
                        "id" => $mangaChapter->getId(),
                        "title" => $mangaChapter->getTitle(),
                        "url" => $mangaChapter->getUrl(),
                        "tomeId" => $mangaChapter->getMangaTome()->getId(),
                    ];
                }
            } else {
                $data = $mangaChapters;
            }

            return $data;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération des chapitres du manga : ". $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Manga $manga
     * @return mixed
     * @throws \Exception
     */
    public function getByMangaWithoutTome(Manga $manga) {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:MangaChapter');
            $mangaChapters = $repo->findBy([
                "manga" => $manga,
                "mangaTome" => null,
            ], ['id' => 'DESC']);

            return $mangaChapters;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération des chapitres sans tome du manga : ". $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param MangaTome $mangaTome
     * @param bool $json
     * @return array
     * @throws \Exception
     */
    public function getByTome(MangaTome $mangaTome, $json = false) {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:MangaChapter');
            $mangaChapters = $repo->findBy([
                "mangaTome" => $mangaTome,
            ], ['id' => 'DESC']);
            if ($json) {
                $data = [];
                foreach ($mangaChapters as $mangaChapter) {
                    $data[] = [
                        "id" => $mangaChapter->getId(),
                        "title" => $mangaChapter->getTitle(),
                        "url" => $mangaChapter->getUrl(),
                        "tomeId" => $mangaChapter->getMangaTome()->getId(),
                    ];
                }
            } else {
                $data = $mangaChapters;
            }
            return $data;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération des chapitres du tome : ". $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param MangaChapter $mangaChapter
     * @return mixed
     * @throws \Exception
     */
    public function getEbook(MangaChapter $mangaChapter) {
        try {
            return $this->serviceMangaEbook->getOneByMangaChapter($mangaChapter);
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération de l'ebook du chapitre : ". $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Manga $manga
     * @param BookChapter $bookChapter
     * @param MangaTome|null $mangaTome
     * @return mixed
     * @throws \Exception
     */
    private function getOneByTitle(Manga $manga, BookChapter $bookChapter, MangaTome $mangaTome = null) {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:MangaChapter');
            $mangaChapter = $repo->findOneBy([
                "manga" => $manga,
                "mangaTome" => $mangaTome,
                "title" => $bookChapter->getTitle(),
            ]);
            return $mangaChapter;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors du controle d'existence du chapitre du tome du titre manga : ". $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}