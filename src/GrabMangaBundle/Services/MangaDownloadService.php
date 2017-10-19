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

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getNextOne() {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:MangaDownload');
            $mangaDownload = $repo->findOneBy([
                "current" => false,
                "finished" => false,
            ], ["id" => "ASC"]);
            return $mangaDownload;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération des téléchargements de l'utilisateur : ". $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getCurrent() {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:MangaDownload');
            $mangaDownload = $repo->findOneBy([
                "current" => true,
            ]);
            return $mangaDownload;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération des téléchargements de l'utilisateur : ". $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Supprime un téléchargement depuis son identifiant
     *
     * @param MangaDownload $mangaDownload
     * @throws \Exception
     */
    public function remove(MangaDownload $mangaDownload) {
        try {
            if (!$mangaDownload) {
                throw new \Exception("Téléchargement inexistant", Response::HTTP_NOT_FOUND);
            }
            $this->em->remove($mangaDownload);
            $this->em->flush();
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de suppression du téléchargement de l'utilisateur : ". $ex->getMessage(), $ex->getCode());
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

    /**
     * récupère le téléchargement courant
     *
     * @param User $user
     * @param bool $json
     * @return array
     * @throws \Exception
     */
    public function getCurrentByUser(User $user, $json = false) {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:MangaDownload');
            $mangaDownload = $repo->findOneBy([
                "user" => $user,
                "current" => true,
            ], ["id" => "DESC"]);
            if ($json) {
                $data = null;
                if ($mangaDownload) {
                    $data = $this->getJsonMangaDownload($mangaDownload);
                }
            } else {
                $data = $mangaDownload;
            }
            return $data;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération des téléchargements de l'utilisateur : ". $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * récupère tous les téléchargements non terminés de l'utilisateur
     *
     * @param User $user
     * @param bool $json
     * @return array
     * @throws \Exception
     */
    public function getAllWaitingByUser(User $user, $json = false) {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:MangaDownload');
            $mangasDownload = $repo->findBy([
                "user" => $user,
                "finished" => false,
            ], ["id" => "ASC"]);
            if ($json) {
                $data = null;
                if (count($mangasDownload) > 0) {
                    $data = [];
                    foreach ($mangasDownload as $mangaDownload) {
                        $data[] = $this->getJsonMangaDownload($mangaDownload);
                    }
                }
            } else {
                $data = $mangasDownload;
            }
            return $data;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération des téléchargements de l'utilisateur : ". $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * récupère tous les téléchargements terminés de l'utilisateur
     *
     * @param User $user
     * @param bool $json
     * @return array|null
     * @throws \Exception
     */
    public function getAllFinishedByUser(User $user, $json = false) {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:MangaDownload');
            $mangasDownload = $repo->findBy([
                "user" => $user,
                "finished" => true,
            ], ["id" => "ASC"]);
            if ($json) {
                $data = null;
                if (count($mangasDownload) > 0) {
                    $data = [];
                    foreach ($mangasDownload as $mangaDownload) {
                        $data[] = $this->getJsonMangaDownload($mangaDownload);
                    }
                }
            } else {
                $data = $mangasDownload;
            }
            return $data;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération des téléchargements de l'utilisateur : ". $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getJsonMangaDownload(MangaDownload $mangaDownload) {
        try {
            $manga = null;
            $mangaTome = null;
            $mangaChapter = null;
            $mangaTitle = null;
            $title = '';
            if ($mangaDownload->getManga()){
                $title = $mangaDownload->getManga()->getTitle();
                $mangaTitle = $mangaDownload->getManga()->getTitle();
                $manga = [
                    'id' => $mangaDownload->getManga()->getId(),
                    'title' => $mangaDownload->getManga()->getTitle(),
                ];
            } elseif ($mangaDownload->getMangaTome()){
                $title = $mangaDownload->getMangaTome()->getTitle();
                $mangaTitle = $mangaDownload->getMangaTome()->getManga()->getTitle();
                $mangaTome = [
                    'id' => $mangaDownload->getMangaTome()->getId(),
                    'title' => $mangaDownload->getMangaTome()->getTitle(),
                    'manga' => [
                        'id' => $mangaDownload->getMangaTome()->getManga()->getId(),
                        'title' => $mangaDownload->getMangaTome()->getManga()->getTitle(),
                    ],
                ];
            } elseif ($mangaDownload->getMangaChapter()){
                $title = $mangaDownload->getMangaChapter()->getTitle();
                $mangaTitle = $mangaDownload->getMangaChapter()->getManga()->getTitle();
                $tome = null;
                if ($mangaDownload->getMangaChapter()->getMangaTome()) {
                    $tome = [
                        'id' => $mangaDownload->getMangaChapter()->getMangaTome()->getId(),
                        'title' => $mangaDownload->getMangaChapter()->getMangaTome()->getTitle(),
                    ];
                }
                $mangaChapter = [
                    'id' => $mangaDownload->getMangaChapter()->getId(),
                    'title' => $mangaDownload->getMangaChapter()->getTitle(),
                    'manga' => [
                        'id' => $mangaDownload->getMangaChapter()->getManga()->getId(),
                        'title' => $mangaDownload->getMangaChapter()->getManga()->getTitle(),
                    ],
                    'mangaTome' => $tome,
                ];
            }
            $progress = intval(($mangaDownload->getCurrentPageDecode() / $mangaDownload->getMaxPage()) * 100);
            $data = [
                "id" => $mangaDownload->getId(),
                "title" => $title,
                "currentPageDecode" => $mangaDownload->getCurrentPageDecode(),
                "currentPagePdf" => $mangaDownload->getCurrentPagePdf(),
                "countPage" => $mangaDownload->getMaxPage(),
                "progress" => $progress,
                "currentZip" => $mangaDownload->getCurrentFileZip(),
                "countZip" => $mangaDownload->getMaxFileZip(),
                "mangaTitle" => $mangaTitle,
                "manga" => $manga,
                "mangaTome" => $mangaTome,
                "mangaChapter" => $mangaChapter,
            ];
            return $data;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de formatage du téléchargement au format json : ". $ex->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



}