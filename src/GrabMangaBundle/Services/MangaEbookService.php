<?php

namespace GrabMangaBundle\Services;

use GrabMangaBundle\Entity\MangaChapter;
use GrabMangaBundle\Entity\MangaEbook;
use GrabMangaBundle\Entity\MangaTome;
use GrabMangaBundle\Generic\BookEbook;
use Symfony\Component\HttpFoundation\Response;

class MangaEbookService {

	private $doctrine;
	private $validator;
	private $serviceMessage;
	private $serviceMangaPage;

    public function __construct($doctrine, $validator, MessageService $serviceMessage,
                                MangaPageService $serviceMangaPage) {
        $this->doctrine = $doctrine;
        $this->validator = $validator;
        $this->serviceMessage = $serviceMessage;
        $this->serviceMangaPage = $serviceMangaPage;
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function getOne($id) {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:MangaEbook');
            $mangaEbook = $repo->find($id);
            if(!$mangaEbook) {
                throw new \Exception("Aucun ebook manga.", Response::HTTP_NOT_FOUND);
            }
            return $mangaEbook;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération du ebook manga : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @param MangaChapter $mangaChapter
     * @param BookEbook|null $bookEbook
     * @throws \Exception
     */
    public function add(MangaChapter $mangaChapter, BookEbook $bookEbook = null) {
        try {
            if ($bookEbook) {
                $em = $this->doctrine->getManager();
                $mangaEbook = $this->getOneByMangaChapter($mangaChapter);
                $mangaEbookExist = false;
                if ($mangaEbook) {
                    $mangaEbookExist = true;
                } else {
                    $mangaEbook = new MangaEbook();
                }
                $mangaEbook->setUrlMask($bookEbook->getUrlMask())
                    ->setMangaChapter($mangaChapter);
                $errors = $this->validator->validate($mangaEbook);
                if (count($errors)>0) {
                    throw new \Exception($this->serviceMessage->formatErreurs($errors), 500);
                }
                if ($mangaEbookExist) {
                    $em->merge($mangaEbook);
                } else {
                    $em->persist($mangaEbook);
                }
                $em->flush();
                $this->serviceMangaPage->add($mangaEbook, $bookEbook->getBookPages());
            }
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de l'ajout du ebook manga : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @param $mangaChapters
     * @return int
     * @throws \Exception
     */
    public function getCountPageByChapters($mangaChapters) {
        try {
            $count = 0;
            foreach ($mangaChapters as $mangaChapter) {
                $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:MangaEbook');
                $mangaEbooks = $repo->findBy([
                    "mangaChapter" => $mangaChapter,
                ]);
                foreach ($mangaEbooks as $mangaEbook) {
                    $mangaPages = $this->serviceMangaPage->getByMangaEbook($mangaEbook);
                    $count += count($mangaPages);
                }
            }
            return $count;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur du calcul du nombre de page du tome : ". $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param MangaChapter $mangaChapter
     * @return mixed
     * @throws \Exception
     */
    public function getOneByMangaChapter(MangaChapter $mangaChapter) {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:MangaEbook');
            $mangaEbook = $repo->findOneBy([
                "mangaChapter" => $mangaChapter,
            ]);
            return $mangaEbook;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération du ebook manga : ". $ex->getMessage(), $ex->getCode());
        }
    }

    public function getMangaPages(MangaEbook $mangaEbook) {
        try {
            return $this->serviceMangaPage->getByMangaEbook($mangaEbook);
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération du ebook manga : ". $ex->getMessage(), $ex->getCode());
        }
    }

}