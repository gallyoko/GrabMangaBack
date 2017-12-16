<?php

namespace GrabMangaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

class TorrentController extends Controller
{
    /**
     * @Rest\View()
     * @Rest\Post("/torrent/search")
     */
    public function searchAction(Request $request) {
        try {
            $search = $this->get('torrent.service')->search($request->getContent());
            return $search;
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

    /**
     * @Rest\View()
     * @Rest\Get("/torrent/categories")
     */
    public function getCategoriesAction() {
        try {
            $search = $this->get('torrent.service')->getCategories();
            return $search;
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

}
