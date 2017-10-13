<?php

namespace GrabMangaBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

class GenerateController extends GrabMangaController
{
    /**
     * @Rest\View()
     * @Rest\Get("/generate/manga/{token}/{id}")
     */
    public function generateMangaAction($id) {
        try {
            $manga = $this->get('manga.service')->getOne($id);
            $download = $this->get('manga_download.service')->saveBook($this->getUser(), $manga);
            //$data = $this->get('generate.service')->generateByBook($manga, $download);
            return $this->setResponse(['downloadId' => $download->getId()]);
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

    /**
     * @Rest\View()
     * @Rest\Get("/generate/tome/{token}/{id}")
     */
    public function generateTomeAction($id) {
        try {
            $tome = $this->get('manga_tome.service')->getOne($id);
            $download = $this->get('manga_download.service')->saveTome($this->getUser(), $tome);
            //$data = $this->get('generate.service')->generateByTome($tome, $download);
            return $this->setResponse(['downloadId' => $download->getId()]);
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

    /**
     * @Rest\View()
     * @Rest\Get("/generate/chapter/{token}/{id}")
     */
    public function generateChapterAction($id) {
        try {
            $chapter = $this->get('manga_chapter.service')->getOne($id);
            $download = $this->get('manga_download.service')->saveChapter($this->getUser(), $chapter);
            //$data = $this->get('generate.service')->generateByChapter($chapter, $download);
            return $this->setResponse(['downloadId' => $download->getId()]);
        } catch (\Exception $ex) {
            return View::create(['message' => $ex->getMessage()], $ex->getCode());
        }
    }

}
