<?php

namespace GrabMangaBundle\Services;

use GrabMangaBundle\Entity\MangaEbook;
use GrabMangaBundle\Entity\MangaPage;
use GrabMangaBundle\Generic\BookPage;
use Symfony\Component\HttpFoundation\Response;

class MangaPageService {

	private $doctrine;
	private $validator;
	private $serviceMessage;

    public function __construct($doctrine, $validator, MessageService $serviceMessage) {
        $this->doctrine = $doctrine;
        $this->validator = $validator;
        $this->serviceMessage = $serviceMessage;
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function getOne($id) {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:MangaPage');
            $mangaPage = $repo->find($id);
            if(!$mangaPage) {
                throw new \Exception("Aucune page ebook.", Response::HTTP_NOT_FOUND);
            }
            return $mangaPage;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération de la page ebook : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @param MangaEbook $mangaEbook
     * @param array $bookPages
     * @throws \Exception
     */
    public function add(MangaEbook $mangaEbook, $bookPages = array()) {
        try {
            if (count($bookPages)>0) {
                $em = $this->doctrine->getManager();
                foreach ($bookPages as $bookPage) {
                    $mangaPage = $this->getMangaPageByMangaEbook($mangaEbook, $bookPage);
                    if (!$mangaPage) {
                        $mangaPage = new MangaPage();
                        $mangaPage->setPage($bookPage->getPage())
                            ->setMangaEbook($mangaEbook);
                        $errors = $this->validator->validate($mangaPage);
                        if (count($errors)==0) {
                            $em->persist($mangaPage);
                        }
                    }
                }
                $em->flush();
            }
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de l'ajout de la page ebook manga : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @param MangaEbook $mangaEbook
     * @param BookPage $bookPage
     * @return mixed
     * @throws \Exception
     */
    public function getMangaPageByMangaEbook(MangaEbook $mangaEbook, BookPage $bookPage) {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:MangaPage');
            $mangaPage = $repo->findOneBy([
                "mangaEbook" => $mangaEbook,
                "page" => $bookPage->getPage(),
            ]);
            return $mangaPage;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération de la page de l'ebook manga : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @param MangaEbook $mangaEbook
     * @return mixed
     * @throws \Exception
     */
    public function getByMangaEbook(MangaEbook $mangaEbook) {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:MangaPage');
            $mangaPages = $repo->findBy([
                "mangaEbook" => $mangaEbook
            ]);
            return $mangaPages;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération des pages de l'ebook manga : ". $ex->getMessage(), $ex->getCode());
        }
    }

}