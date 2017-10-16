<?php

namespace GrabMangaBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

class ArchiveController extends GrabMangaController
{
    /**
     * Lance le téléchargement d'un manga, tome ou chapitre en fonction de son identifiant
     *
     * @param integer $id
     * @return Response
     */
    public function downloadAction($id) {
        try {
            $downloadManga = $this->get('manga_download.service')->getOne($id);
            $path = $this->get('generate.service')->getFileDownload($downloadManga);
            $name = basename($path);
            return $this->launch($path, $name);
        } catch (\Exception $ex) {
            return new Response("Impossible de générer le document agent : ".$ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Lance le téléchargement d'un fichier
     *
     * @param string $path
     * @param string $name
     * @return Response
     * @throws \Exception
     */
    private function launch($path, $name) {
        try {
            $response = new Response();
            $response->headers->set('Cache-Control', 'private');
            $response->headers->set('Content-type', mime_content_type($path));
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $name . '";');
            $response->headers->set('Content-length', filesize($path));
            $response->sendHeaders();
            $response->setContent(file_get_contents($path));
            return $response;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de lancement du téléchargement", 500);
        }
    }
}
