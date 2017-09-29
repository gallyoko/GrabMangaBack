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

    public function __construct($doctrine, $validator, $serviceMessage) {
        $this->doctrine = $doctrine;
        $this->validator = $validator;
        $this->serviceMessage = $serviceMessage;
    }

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
                $mangaEbook->setListPage(json_encode($bookEbook->getListPage()))
                    ->setUrlMask($bookEbook->getUrlMask())
                    ->setMangaChapter($mangaChapter)
                    ->setListFormat(json_encode($bookEbook->getListFormat()));
                $errors = $this->validator->validate($mangaEbook);
                if (count($errors)==0) {
                    if ($mangaEbookExist) {
                        $em->merge($mangaEbook);
                    } else {
                        $em->persist($mangaEbook);
                    }
                    $em->flush();
                }
            }
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de l'ajout du ebook manga : ". $ex->getMessage(), $ex->getCode());
        }
    }

    public function getCountPageByChapters($mangaChapters) {
        try {
            $count = 0;
            foreach ($mangaChapters as $mangaChapter) {
                $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:MangaEbook');
                $mangaEbooks = $repo->findBy([
                    "mangaChapter" => $mangaChapter,
                ]);
                foreach ($mangaEbooks as $mangaEbook) {
                    echo $mangaEbook->getPageMax();
                    die;
                    $count += (int) $mangaEbook->getPageMax();
                }
            }
            return $count;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur du calcul du nombre de page du tome : ". $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getOneByMangaChapter(MangaChapter $mangaChapter) {
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

}