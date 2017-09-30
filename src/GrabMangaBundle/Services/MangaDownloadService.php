<?php

namespace GrabMangaBundle\Services;

use Symfony\Component\HttpFoundation\Response;
use GrabMangaBundle\Entity\Manga;
use GrabMangaBundle\Entity\MangaTome;
use GrabMangaBundle\Entity\MangaChapter;
use GrabMangaBundle\Entity\MangaDownload;

class MangaDownloadService {

    private $mangaDownload;
	private $doctrine;
	private $em;
	private $validator;
	private $serviceMessage;
    private $serviceMangaEbook;
    private $serviceMangaChapter;
    private $user;

    public function __construct($doctrine, $validator, $serviceMessage, $serviceMangaEbook, $serviceMangaChapter) {
        $this->mangaDownload = null;
        $this->doctrine = $doctrine;
        $this->em = $doctrine->getManager();
        $this->validator = $validator;
        $this->serviceMessage = $serviceMessage;
        $this->serviceMangaEbook = $serviceMangaEbook;
        $this->serviceMangaChapter = $serviceMangaChapter;
        $repo = $doctrine->getManager()->getRepository('GrabMangaBundle:User');
        $this->user = $repo->find(1);
	}

    public function setMangaDownload(MangaDownload $mangaDownload) {
        try {
            $this->mangaDownload = $mangaDownload;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur d'initialisation du téléchargement : ". $ex->getMessage(), $ex->getCode());
        }
    }

    public function getMangaDownload() {
        try {
            return $this->mangaDownload;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération du téléchargement : ". $ex->getMessage(), $ex->getCode());
        }
    }

    public function getOne($id) {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:MangaDownload');
            $mangaDownload = $repo->find($id);
            if(!$mangaDownload) {
                throw new \Exception("Aucun téléchargement pour ce manga.", Response::HTTP_NOT_FOUND);
            }
            return $mangaDownload;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération du téléchargement manga : ". $ex->getMessage(), $ex->getCode());
        }
    }

    public function saveBook(Manga $manga) {
        try {
            $mangaDownload = new MangaDownload();
            $mangaChapters = $this->serviceMangaChapter->getByManga($manga);
            $maxPages = (int) $this->serviceMangaEbook->getCountPageByChapters($mangaChapters);
            $mangaDownload->setManga($manga)
                ->setUser($this->user)
                ->setMaxPage($maxPages);
            $errors = $this->validator->validate($mangaDownload);
            if (count($errors)>0) {
                throw new \Exception($this->serviceMessage->formatErreurs($errors), 500);
            }
            $this->em->persist($mangaDownload);
            $this->em->flush();
            return $mangaDownload;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur d'insertion du téléchargement book : ". $ex->getMessage(), $ex->getCode());
        }
    }

    public function saveTome(MangaTome $mangaTome) {
        try {
            $mangaDownload = new MangaDownload();
            $mangaChapters = $this->serviceMangaChapter->getByTome($mangaTome);
            $maxPages = (int) $this->serviceMangaEbook->getCountPageByChapters($mangaChapters);
            $mangaDownload->setMangaTome($mangaTome)
                ->setUser($this->user)
                ->setMaxPage($maxPages);
            $errors = $this->validator->validate($mangaDownload);
            if (count($errors)>0) {
                throw new \Exception($this->serviceMessage->formatErreurs($errors), 500);
            }
            $this->em->persist($mangaDownload);
            $this->em->flush();
            return $mangaDownload;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur d'insertion du téléchargement tome : ". $ex->getMessage(), $ex->getCode());
        }
    }

    public function saveChapter(MangaChapter $mangaChapter) {
        try {
            $mangaDownload = new MangaDownload();
            $maxPages = (int) $this->serviceMangaEbook->getCountPageByChapters([$mangaChapter]);
            $mangaDownload->setMangaChapter($mangaChapter)
                ->setUser($this->user)
                ->setMaxPage($maxPages);
            $errors = $this->validator->validate($mangaDownload);
            if (count($errors)>0) {
                throw new \Exception($this->serviceMessage->formatErreurs($errors), 500);
            }
            $this->em->persist($mangaDownload);
            $this->em->flush();
            return $mangaDownload;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur d'insertion du téléchargement chapitre : ". $ex->getMessage(), $ex->getCode());
        }
    }

    public function tagCurrent() {
        try {
            if (!$this->mangaDownload) {
                throw new \Exception("Aucun téléchargement en cours", 404);
            }
            $this->mangaDownload->setCurrent(true);
            $errors = $this->validator->validate($this->mangaDownload);
            if (count($errors)>0) {
                throw new \Exception($this->serviceMessage->formatErreurs($errors), 500);
            }
            $this->em->merge($this->mangaDownload);
            $this->em->flush();
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de la mise à jour du tag courant : ". $ex->getMessage(), $ex->getCode());
        }
    }

    public function tagFinished() {
        try {
            if (!$this->mangaDownload) {
                throw new \Exception("Aucun téléchargement en cours", 404);
            }
            $maxPages = $this->mangaDownload->getMaxPage();
            $this->mangaDownload->setCurrent(false);
            $this->mangaDownload->setFinished(true);
            $this->mangaDownload->setCurrentPagePdf($maxPages);
            $errors = $this->validator->validate($this->mangaDownload);
            if (count($errors)>0) {
                throw new \Exception($this->serviceMessage->formatErreurs($errors), 500);
            }
            $this->em->merge($this->mangaDownload);
            $this->em->flush();
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de la mise à jour du tag terminé : ". $ex->getMessage(), $ex->getCode());
        }
    }

    public function setCurrentPageDecode($num) {
        try {
            if (!$this->mangaDownload) {
                throw new \Exception("Aucun téléchargement en cours", 404);
            }
            $this->mangaDownload->setCurrentPageDecode($num);
            $errors = $this->validator->validate($this->mangaDownload);
            if (count($errors)>0) {
                throw new \Exception($this->serviceMessage->formatErreurs($errors), 500);
            }
            $this->em->merge($this->mangaDownload);
            $this->em->flush();
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de la mise à jour de la page décodée en cours : ". $ex->getMessage(), $ex->getCode());
        }
    }

    public function getCurrentPageDecode() {
        try {
            if (!$this->mangaDownload) {
                throw new \Exception("Aucun téléchargement en cours", 404);
            }
            return $this->mangaDownload->getCurrentPageDecode();
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de la récupération de la page décodée en cours : ". $ex->getMessage(), $ex->getCode());
        }
    }

    public function setCurrentPagePdf($num) {
        try {
            if (!$this->mangaDownload) {
                throw new \Exception("Aucun téléchargement en cours", 404);
            }
            $this->mangaDownload->setCurrentPagePdf($num);
            $errors = $this->validator->validate($this->mangaDownload);
            if (count($errors)>0) {
                throw new \Exception($this->serviceMessage->formatErreurs($errors), 500);
            }
            $this->em->merge($this->mangaDownload);
            $this->em->flush();
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de la mise à jour de la page pdf en cours : ". $ex->getMessage(), $ex->getCode());
        }
    }

    public function getCurrentPagePdf() {
        try {
            if (!$this->mangaDownload) {
                throw new \Exception("Aucun téléchargement en cours", 404);
            }
            return $this->mangaDownload->getCurrentPagePdf();
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de la récupération de la page pdf en cours : ". $ex->getMessage(), $ex->getCode());
        }
    }

}