<?php

namespace GrabMangaBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

class MangaDownloadController extends GrabMangaController
{
    /**
     * @Rest\View()
     * @Rest\Get("/downloads/user/{token}")
     */
    public function getDownloadUserAction() {
        try {
            $data = $this->get('manga_download.service')->getByUser($this->getUser(), true);
            return $this->setResponse($data);
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

    /**
     * @Rest\View()
     * @Rest\Get("/download/user/current/{token}")
     */
    public function getDownloadUserCurrentAction() {
        try {
            $data = $this->get('manga_download.service')->getCurrentByUser($this->getUser(), true);
            return $this->setResponse($data);
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

    /**
     * @Rest\View()
     * @Rest\Get("/downloads/user/waiting/{token}")
     */
    public function getDownloadUserWaitingAction() {
        try {
            $data = $this->get('manga_download.service')->getAllWaitingByUser($this->getUser(), true);
            return $this->setResponse($data);
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

    /**
     * @Rest\View()
     * @Rest\Get("/downloads/user/finished/{token}")
     */
    public function getDownloadUserFinishedAction() {
        try {
            $data = $this->get('manga_download.service')->getAllFinishedByUser($this->getUser(), true);
            return $this->setResponse($data);
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

    /**
     * @Rest\View()
     * @Rest\Delete("/download/remove/{token}/{id}")
     */
    public function removeDownloadAction($id) {
        try {
            $downloadManga = $this->get('manga_download.service')->getOne($id);
            $this->get('generate.service')->remove($downloadManga);
            return $this->setResponse();
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

}
