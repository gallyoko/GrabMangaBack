<?php

namespace GrabMangaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

class GenerateController extends Controller
{
    /**
     * @Rest\View()
     * @Rest\Get("/generate/manga/{id}")
     */
    public function generateMangaAction($id) {
        try {
            $manga = $this->get('manga.service')->getOne($id);
            $download = $this->get('manga_download.service')->saveBook($manga);
            return $this->get('generate.service')->generateByBook($manga, $download);
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

    /**
     * @Rest\View()
     * @Rest\Get("/generate/tome/{id}")
     */
    public function generateTomeAction($id) {
        try {
            $tome = $this->get('manga_tome.service')->getOne($id);
            $download = $this->get('manga_download.service')->saveTome($tome);
            return $this->get('generate.service')->generateByTome($tome, $download);
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

    /**
     * @Rest\View()
     * @Rest\Get("/generate/chapter/{id}")
     */
    public function generateChapterAction($id) {
        try {
            $chapter = $this->get('manga_chapter.service')->getOne($id);
            $download = $this->get('manga_download.service')->saveChapter($chapter);
            return $this->get('generate.service')->generateByChapter($chapter, $download);
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

}
