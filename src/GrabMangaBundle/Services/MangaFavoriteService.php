<?php

namespace GrabMangaBundle\Services;

use Symfony\Component\HttpFoundation\Response;
use GrabMangaBundle\Entity\User;
use GrabMangaBundle\Entity\MangaFavorite;
use GrabMangaBundle\Entity\Manga;

class MangaFavoriteService {

	private $doctrine;
	private $validator;
	private $serviceMessage;
    private $serviceManga;

    /**
     * MangaService constructor.
     *
     * @param $doctrine
     * @param $validator
     * @param MessageService $serviceMessage
     * @param MangaService $serviceManga
     */
	public function __construct($doctrine, $validator, MessageService $serviceMessage,
            MangaService $serviceManga) {
		$this->doctrine = $doctrine;
		$this->validator = $validator;
		$this->serviceMessage = $serviceMessage;
        $this->serviceManga = $serviceManga;
	}

    /**
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function getOne($id) {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:MangaFavorite');
            $manga = $repo->find($id);
            if(!$manga) {
                throw new \Exception("Aucun favori.", Response::HTTP_NOT_FOUND);
            }
            return $manga;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération du favori : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getList() {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:MangaFavorite');
            $data = $repo->findAll();
            return $data;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération de la liste des favoris : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @param User $user
     * @param $json
     * @throws \Exception
     */
    public function add(User $user, $json) {
        try {
            $data = json_decode($json);
            if (!isset($data->mangaId)) {
                throw new \Exception("Veuillez fournir un identifiant manga.", Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $manga = $this->serviceManga->getOne($data->mangaId);
            if ($this->getOneByManga($user, $manga)) {
                throw new \Exception("Ce favori existe déjà.", Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $mangaFavorite = new MangaFavorite();
            $mangaFavorite->setUser($user)
                ->setManga($manga);
            $errors = $this->validator->validate($mangaFavorite);
            if (count($errors)>0) {
                throw new \Exception($this->serviceMessage->formatErreurs($errors), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $em = $this->doctrine->getManager();
            $em->persist($mangaFavorite);
            $em->flush();
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors de l'enregistrement du favori : ".$ex->getMessage(), $ex->getCode());
        }
    }

    public function remove(MangaFavorite $mangaFavorite) {
        try {
            if (!$mangaFavorite) {
                throw new \Exception("Favori inexistant", Response::HTTP_NOT_FOUND);
            }
            $em = $this->doctrine->getManager();
            $em->remove($mangaFavorite);
            $em->flush();
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de suppression du favori : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @param User $user
     * @param Manga $manga
     * @return mixed
     * @throws \Exception
     */
    private function getOneByManga(User $user, Manga $manga) {
        try {
            $repo = $this->doctrine->getManager()->getRepository('GrabMangaBundle:MangaFavorite');
            $mangaFavorite = $repo->findOneBy([
                "user" => $user,
                "manga" => $manga,
            ]);
            return $mangaFavorite;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération du favori manga : ". $ex->getMessage(), $ex->getCode());
        }
    }

}