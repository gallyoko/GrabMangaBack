<?php

namespace GrabMangaBundle\Services;

use GrabMangaBundle\Entity\MangaChapter;
use GrabMangaBundle\Entity\MangaEbook;
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
                $mangaEbook->setFormat($bookEbook->getFormat())
                    ->setPageMask($bookEbook->getPageMask())
                    ->setPageMax($bookEbook->getPageMax())
                    ->setPageMin($bookEbook->getPageMin())
                    ->setUrlMask($bookEbook->getUrlMask())
                    ->setMangaChapter($mangaChapter);
                $errors = $this->validator->validate($mangaEbook);
                if (count($errors)>0) {
                    throw new \Exception($this->serviceMessage->formatErreurs($errors));
                }
                if ($mangaEbookExist) {
                    $em->merge($mangaEbook);
                } else {
                    $em->persist($mangaEbook);
                }
                $em->flush();
            }
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de l'ajout du ebook manga : ". $ex->getMessage(), $ex->getCode());
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