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
	
	public function __construct($doctrine, $validator, $serviceMessage) {
		$this->doctrine = $doctrine;
		$this->validator = $validator;
		$this->serviceMessage = $serviceMessage;
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
                    throw new \Exception($this->serviceMessage->formatErreurs($errors));
                }
                if ($mangaChapterExist) {
                    $em->merge($mangaChapter);
                } else {
                    $em->persist($mangaChapter);
                }
                $em->flush();
            }
        } catch (\Exception $ex) {
            throw new \Exception("Erreur d'enregistrement d'un chapitre d'un tome du manga : ". $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
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