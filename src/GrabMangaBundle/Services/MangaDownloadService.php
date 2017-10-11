<?php

namespace GrabMangaBundle\Services;

use Symfony\Component\HttpFoundation\Response;
use GrabMangaBundle\Entity\Manga;
use GrabMangaBundle\Entity\MangaTome;
use GrabMangaBundle\Entity\MangaChapter;
use GrabMangaBundle\Entity\MangaDownload;
use GrabMangaBundle\Entity\User;

class MangaDownloadService {

    private $mangaDownload;
	private $doctrine;
	private $em;
	private $validator;
	private $serviceMessage;
    private $serviceMangaEbook;
    private $serviceMangaChapter;

    /**
     * MangaDownloadService constructor.
     *
     * @param $doctrine
     * @param $validator
     * @param MessageService $serviceMessage
     * @param MangaEbookService $serviceMangaEbook
     * @param MangaChapterService $serviceMangaChapter
     */
    public function __construct($doctrine, $validator, MessageService $serviceMessage,
                                MangaEbookService $serviceMangaEbook,
                                MangaChapterService $serviceMangaChapter) {
        $this->mangaDownload = null;
        $this->doctrine = $doctrine;
        $this->em = $doctrine->getManager();
        $this->validator = $validator;
        $this->serviceMessage = $serviceMessage;
        $this->serviceMangaEbook = $serviceMangaEbook;
        $this->serviceMangaChapter = $serviceMangaChapter;
	}

    /**
     * @param MangaDownload $mangaDownload
     * @throws \Exception
     */
    public function setMangaDownload(MangaDownload $mangaDownload) {
        try {
            $this->mangaDownload = $mangaDownload;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur d'initialisation du téléchargement : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @return null
     * @throws \Exception
     */
    public function getMangaDownload() {
        try {
            return $this->mangaDownload;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération du téléchargement : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Exception
     */
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

    /**
     * @param User $user
     * @param Manga $manga
     * @return MangaDownload
     * @throws \Exception
     */
    public function saveBook(User $user, Manga $manga) {
        try {
            $mangaDownload = new MangaDownload();
            $mangaChapters = $this->serviceMangaChapter->getByManga($manga);
            $maxPages = (int) $this->serviceMangaEbook->getCountPageByChapters($mangaChapters);
            $mangaDownload->setManga($manga)
                ->setUser($user)
                ->setMaxPage($maxPages);
            $errors = $this->validator->validate($mangaDownload);
            if (count($errors)>0) {
                throw new \Exception($this->serviceMessage->formatErreurs($errors), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $this->em->persist($mangaDownload);
            $this->em->flush();
            return $mangaDownload;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur d'insertion du téléchargement book : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @param User $user
     * @param MangaTome $mangaTome
     * @return MangaDownload
     * @throws \Exception
     */
    public function saveTome(User $user, MangaTome $mangaTome) {
        try {
            $mangaDownload = new MangaDownload();
            $mangaChapters = $this->serviceMangaChapter->getByTome($mangaTome);
            $maxPages = (int) $this->serviceMangaEbook->getCountPageByChapters($mangaChapters);
            $mangaDownload->setMangaTome($mangaTome)
                ->setUser($user)
                ->setMaxPage($maxPages);
            $errors = $this->validator->validate($mangaDownload);
            if (count($errors)>0) {
                throw new \Exception($this->serviceMessage->formatErreurs($errors), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $this->em->persist($mangaDownload);
            $this->em->flush();
            return $mangaDownload;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur d'insertion du téléchargement tome : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @param User $user
     * @param MangaChapter $mangaChapter
     * @return MangaDownload
     * @throws \Exception
     */
    public function saveChapter(User $user, MangaChapter $mangaChapter) {
        try {
            $mangaDownload = new MangaDownload();
            $maxPages = (int) $this->serviceMangaEbook->getCountPageByChapters([$mangaChapter]);
            $mangaDownload->setMangaChapter($mangaChapter)
                ->setUser($user)
                ->setMaxPage($maxPages);
            $errors = $this->validator->validate($mangaDownload);
            if (count($errors)>0) {
                throw new \Exception($this->serviceMessage->formatErreurs($errors), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $this->em->persist($mangaDownload);
            $this->em->flush();
            return $mangaDownload;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur d'insertion du téléchargement chapitre : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @throws \Exception
     */
    public function tagCurrent() {
        try {
            if (!$this->mangaDownload) {
                throw new \Exception("Aucun téléchargement en cours", Response::HTTP_NOT_FOUND);
            }
            $this->mangaDownload->setCurrent(true);
            $errors = $this->validator->validate($this->mangaDownload);
            if (count($errors)>0) {
                throw new \Exception($this->serviceMessage->formatErreurs($errors), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $this->em->merge($this->mangaDownload);
            $this->em->flush();
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de la mise à jour du tag courant : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @throws \Exception
     */
    public function tagFinished() {
        try {
            if (!$this->mangaDownload) {
                throw new \Exception("Aucun téléchargement en cours", Response::HTTP_NOT_FOUND);
            }
            $maxPages = $this->mangaDownload->getMaxPage();
            $this->mangaDownload->setCurrent(false);
            $this->mangaDownload->setFinished(true);
            $this->mangaDownload->setCurrentPagePdf($maxPages);
            $errors = $this->validator->validate($this->mangaDownload);
            if (count($errors)>0) {
                throw new \Exception($this->serviceMessage->formatErreurs($errors), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $this->em->merge($this->mangaDownload);
            $this->em->flush();
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de la mise à jour du tag terminé : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @param $num
     * @throws \Exception
     */
    public function setCurrentPageDecode($num) {
        try {
            if (!$this->mangaDownload) {
                throw new \Exception("Aucun téléchargement en cours", Response::HTTP_NOT_FOUND);
            }
            $this->mangaDownload->setCurrentPageDecode($num);
            $errors = $this->validator->validate($this->mangaDownload);
            if (count($errors)>0) {
                throw new \Exception($this->serviceMessage->formatErreurs($errors), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $this->em->merge($this->mangaDownload);
            $this->em->flush();
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de la mise à jour de la page décodée en cours : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getCurrentPageDecode() {
        try {
            if (!$this->mangaDownload) {
                throw new \Exception("Aucun téléchargement en cours", Response::HTTP_NOT_FOUND);
            }
            return $this->mangaDownload->getCurrentPageDecode();
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de la récupération de la page décodée en cours : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @param $num
     * @throws \Exception
     */
    public function setCurrentPagePdf($num) {
        try {
            if (!$this->mangaDownload) {
                throw new \Exception("Aucun téléchargement en cours", Response::HTTP_NOT_FOUND);
            }
            $this->mangaDownload->setCurrentPagePdf($num);
            $errors = $this->validator->validate($this->mangaDownload);
            if (count($errors)>0) {
                throw new \Exception($this->serviceMessage->formatErreurs($errors), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $this->em->merge($this->mangaDownload);
            $this->em->flush();
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de la mise à jour de la page pdf en cours : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getCurrentPagePdf() {
        try {
            if (!$this->mangaDownload) {
                throw new \Exception("Aucun téléchargement en cours", Response::HTTP_NOT_FOUND);
            }
            return $this->mangaDownload->getCurrentPagePdf();
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de la récupération de la page pdf en cours : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @param $num
     * @throws \Exception
     */
    public function setMaxFileZip($num) {
        try {
            if (!$this->mangaDownload) {
                throw new \Exception("Aucun téléchargement en cours", Response::HTTP_NOT_FOUND);
            }
            $this->mangaDownload->setMaxFileZip($num);
            $errors = $this->validator->validate($this->mangaDownload);
            if (count($errors)>0) {
                throw new \Exception($this->serviceMessage->formatErreurs($errors), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $this->em->merge($this->mangaDownload);
            $this->em->flush();
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de la mise à jour du nombre max de fichier zip : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @param $num
     * @throws \Exception
     */
    public function setCurrentFileZip($num) {
        try {
            if (!$this->mangaDownload) {
                throw new \Exception("Aucun téléchargement en cours", Response::HTTP_NOT_FOUND);
            }
            $this->mangaDownload->setCurrentFileZip($num);
            $errors = $this->validator->validate($this->mangaDownload);
            if (count($errors)>0) {
                throw new \Exception($this->serviceMessage->formatErreurs($errors), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $this->em->merge($this->mangaDownload);
            $this->em->flush();
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de la mise à jour du fichier zip en cours : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /***************************************************
     ********************* API *************************
     ***************************************************/

    /**
     * @param User $user
     * @param bool $json
     * @return array
     * @throws \Exception
     */
    public function getByUser(User $user, $json = false) {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:MangaDownload');
            $mangaDownloads = $repo->findBy([
                "user" => $user,
            ]);
            if ($json) {
                $data = [];
                foreach ($mangaDownloads as $mangaDownload) {
                    $data[] = [
                        "id" => $mangaDownload->getId(),
                    ];
                }
            } else {
                $data = $mangaDownloads;
            }
            return $data;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération des téléchargements des utilisateurs : ". $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}