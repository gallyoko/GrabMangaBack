<?php

namespace GrabMangaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

class MangaFavoriteController extends GrabMangaController
{
    /**
     * @Rest\View()
     * @Rest\Get("/favorites/{token}")
     */
    public function getFavoriteAllAction() {
        try {
            $data = $this->get('manga_favorite.service')->getList();
            return $this->setResponse($data);
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED)
     * @Rest\Post("/favorite/{token}")
     */
    public function addFavoriteAction(Request $request) {
        try {
            $this->get('manga_favorite.service')->add($this->getUser(),
                $request->getContent());
            return $this->setResponse();
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

    /**
     * @Rest\View()
     * @Rest\Delete("/favorite/remove/{token}/{id}")
     */
    public function removeFavoriteAction($id) {
        try {
            $mangaFavorite = $this->get('manga_favorite.service')->getOne($id);
            $this->get('manga_favorite.service')->remove($mangaFavorite);
            return $this->setResponse();
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

}
