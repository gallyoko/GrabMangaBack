<?php

namespace GrabMangaBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

class MangaChapterController extends GrabMangaController
{
    /**
     * @Rest\View()
     * @Rest\Get("/chapters/manga/{token}/{mangaId}")
     */
    public function getChaptersByMangaAction($mangaId) {
        try {
            $manga = $this->get('manga.service')->getOne($mangaId);
            $data = $this->get('manga_chapter.service')->getByManga($manga, true);
            return $this->setResponse($data);
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

    /**
     * @Rest\View()
     * @Rest\Get("/chapters/tome/{token}/{tomeId}")
     */
    public function getChaptersByTomeAction($tomeId) {
        try {
            $tome = $this->get('manga_tome.service')->getOne($tomeId);
            $data = $this->get('manga_chapter.service')->getByTome($tome);
            return $this->setResponse($data);
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

    /**
     * @Rest\View()
     * @Rest\Get("/chapter/info/{token}/{id}")
     */
    public function getChapterInfoAction($id) {
        try {
            $data = $this->get('manga_chapter.service')->getInfo($id);
            return $this->setResponse($data);
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }
}
