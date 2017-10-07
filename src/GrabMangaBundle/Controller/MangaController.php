<?php

namespace GrabMangaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

class MangaController extends Controller
{
    public function setContainer(ContainerInterface $container = null) {
        try {
            $this->container = $container;
            $request = $container->get('request_stack')->getCurrentRequest();
            if (!$request->attributes->has('token')) {
                throw new \Exception("Erreur de token", Response::HTTP_UNAUTHORIZED);
            }
            $token = $request->attributes->get('token');
        } catch (\Exception $ex) {
            throw new \Exception("Alerte sécurité : ".$ex->getMessage(), $ex->getCode());
        }

    }

    /**
     * @Rest\View()
     * @Rest\Get("/mangas")
     */
    public function getAllAction() {
        try {
            return $this->get('manga.service')->getList();
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
            return $this->get('manga_tome.service')->getByManga($manga, true);
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

    /**
     * @Rest\View()
     * @Rest\Get("/manga/chapters/{mangaId}")
     */
    public function getChaptersAction($mangaId) {
        try {
            $manga = $this->get('manga.service')->getOne($mangaId);
            return $this->get('manga_chapter.service')->getByManga($manga, true);
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

    /**
     * @Rest\View()
     * @Rest\Get("/manga/tome/chapters/{tomeId}")
     */
    public function getChaptersTomeAction($tomeId) {
        try {
            $tome = $this->get('manga_tome.service')->getOne($tomeId);
            return $this->get('manga_chapter.service')->getByTome($tome);
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }
}
