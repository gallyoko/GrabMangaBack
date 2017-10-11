<?php

namespace GrabMangaBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

class MangaTomeController extends GrabMangaController
{
    /**
     * @Rest\View()
     * @Rest\Get("/tomes/manga/{token}/{mangaId}")
     */
    public function getTomesByMangaAction($mangaId) {
        try {
            $manga = $this->get('manga.service')->getOne($mangaId);
            $data = $this->get('manga_tome.service')->getByManga($manga, true);
            return $this->setResponse($data);
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

    /**
     * @Rest\View()
     * @Rest\Get("/tome/info/{token}/{id}")
     */
    public function getTomeInfoAction($id) {
        try {
            $data = $this->get('manga_tome.service')->getInfo($id);
            return $this->setResponse($data);
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }
}
