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

    public function __construct($doctrine, $validator, $serviceMessage, MangaEbookService $serviceMangaEbook) {
        $this->doctrine = $doctrine;
        $this->validator = $validator;
        $this->serviceMessage = $serviceMessage;
        $this->serviceMangaEbook = $serviceMangaEbook;
    }

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
                $em->flush();
                $this->serviceMangaEbook->add($mangaChapter, $bookChapter->getBookEbook());
            }
        } catch (\Exception $ex) {
            throw new \Exception("Erreur d'enregistrement d'un chapitre d'un tome du manga : ". $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

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

    public function getByMangaWithoutTome(Manga $manga) {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:MangaChapter');
            $mangaChapters = $repo->findBy([
                "manga" => $manga,
                "mangaTome" => null,
            ]);

            return $mangaChapters;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération des chapitres sans tome du manga : ". $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getByTome(MangaTome $mangaTome, $json = false) {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:MangaChapter');
            $mangaChapters = $repo->findBy([
                "mangaTome" => $mangaTome,
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
            throw new \Exception("Erreur de récupération des chapitres du tome : ". $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getEbook(MangaChapter $mangaChapter) {
        try {
            return $this->serviceMangaEbook->getOneByMangaChapter($mangaChapter);
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération de l'ebook du chapitre : ". $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

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