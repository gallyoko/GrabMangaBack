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

}
