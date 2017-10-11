<?php

namespace GrabMangaBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

class MangaController extends GrabMangaController
{
    /**
     * @Rest\View()
     * @Rest\Get("/mangas/{token}")
     */
    public function getMangaAllAction() {
        try {
            $data = $this->get('manga.service')->getList();
            return $this->setResponse($data);
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

    /**
     * @Rest\View()
     * @Rest\Get("/mangas/search/{token}/{search}")
     */
    public function getMangaSearchAction($search) {
        try {
            $data = $this->get('manga.service')->findByTitle($search, true);
            return $this->setResponse($data);
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

    /**
     * @Rest\View()
     * @Rest\Get("/mangas/search/begin/{token}/{search}")
     */
    public function getMangaSearchBeginAction($search) {
        try {
            $data = $this->get('manga.service')->findByTitleBeginBy($search, true);
            return $this->setResponse($data);
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

    /**
     * @Rest\View()
     * @Rest\Get("/manga/info/{token}/{id}")
     */
    public function getMangaInfoAction($id) {
        try {
            $data = $this->get('manga.service')->getInfo($id, true);
            return $this->setResponse($data);
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }
}
