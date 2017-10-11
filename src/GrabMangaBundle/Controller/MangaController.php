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
    public function getAllAction() {
        try {
            $data = $this->get('manga.service')->getList();
            return $this->setResponse($data);
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

    /**
     * @Rest\View()
     * @Rest\Get("/manga/tomes/{token}/{mangaId}")
     */
    public function getTomesAction($mangaId) {
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
     * @Rest\Get("/manga/chapters/{token}/{mangaId}")
     */
    public function getChaptersAction($mangaId) {
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
     * @Rest\Get("/manga/tome/chapters/{token}/{tomeId}")
     */
    public function getChaptersTomeAction($tomeId) {
        try {
            $tome = $this->get('manga_tome.service')->getOne($tomeId);
            $data = $this->get('manga_chapter.service')->getByTome($tome);
            return $this->setResponse($data);
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }
}
