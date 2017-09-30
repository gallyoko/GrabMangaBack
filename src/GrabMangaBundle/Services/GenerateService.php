<?php

namespace GrabMangaBundle\Services;

use GrabMangaBundle\Entity\MangaTome;
use GrabMangaBundle\Entity\MangaChapter;
use GrabMangaBundle\Entity\MangaDownload;

class GenerateService {
    private $doctrine;
    private $em;
    private $validator;
    private $serviceMessage;
    private $serviceMangaDownload;
    private $serviceMangaChapter;
    private $dirSrc;
    private $dirDest;
    private $dirPdf;

    public function __construct($doctrine, $validator, $serviceMessage, MangaDownloadService $serviceMangaDownload,
                                MangaChapterService $serviceMangaChapter, $rootDir, $path) {
        $this->doctrine = $doctrine;
        $this->em = $doctrine->getManager();
        $this->validator = $validator;
        $this->serviceMangaDownload = $serviceMangaDownload;
        $this->serviceMangaChapter = $serviceMangaChapter;
        $this->serviceMessage = $serviceMessage;
        $this->dirSrc = $rootDir.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.
            $path['ebook']['src'];
        $this->dirDest = $rootDir.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.
            $path['ebook']['dst'];
        $this->dirPdf = $rootDir.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.
            $path['ebook']['pdf'];
    }

    public function generateByTome(MangaTome $tome, MangaDownload $download) {
        try {
            set_time_limit(0);
            $timestampIn = time();
            $this->serviceMangaDownload->setMangaDownload($download);
            $this->checkDirectories();
            $this->cleanSrc();
            $this->serviceMangaDownload->tagCurrent();
            $this->aspireTome($tome);
            $this->getPdfTomeName($tome);
            $pdfFilename = $this->getPdfTomeName($tome);
            $this->imageToPdf($pdfFilename);
            $this->cleanDest();
            $this->serviceMangaDownload->tagFinished();
            gc_collect_cycles();
            $timestamp = time() - $timestampIn;
            return ['timeElapsed' => $timestamp];
        } catch (\Exception $ex) {
            throw new \Exception("Erreur du contrôle des répertoires temporaires : ". $ex->getMessage(), $ex->getCode());
        }
    }

    private function aspireTome(MangaTome $tome) {
        try {
            $chapters = $this->serviceMangaChapter->getByTome($tome);
            $numPageDecode = 0;
            foreach ($chapters as $chapter) {
                $this->checkChapterDirectories($chapter);
                $mangaEbook = $this->serviceMangaChapter->getEbook($chapter);
                $pages = json_decode($mangaEbook->getListPage());
                $formats = json_decode($mangaEbook->getListFormat());
                foreach ($pages as $page) {
                    $url = str_replace(' ', '%20',$mangaEbook->getUrlMask()) .
                        $page .'.'.$formats[0];
                    $fileTmp = $this->dirSrc . DIRECTORY_SEPARATOR . $chapter->getId() .
                        DIRECTORY_SEPARATOR . $page .'.'.$formats[0];
                    $fileEnd = $this->dirDest . DIRECTORY_SEPARATOR . $chapter->getId() .
                        DIRECTORY_SEPARATOR . $page .'.'.$formats[0];
                    try {
                        $current = imagecreatefromjpeg($url);
                        if ($current) {
                            imagejpeg($current, $fileTmp);
                            imagedestroy($current);
                            // $decode = $this->decodeImageJpg($fileTmp, $chapter['id'], $nbChapter);
                            copy($fileTmp, $fileEnd);
                            unlink($fileTmp);
                        }
                    } catch (\Exception $ex) {
                        //
                    }
                    $numPageDecode ++;
                    $this->serviceMangaDownload->setCurrentPageDecode($numPageDecode);
                }
                rmdir($this->dirSrc . DIRECTORY_SEPARATOR . $chapter->getId());
            }
            gc_collect_cycles();
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors de l'aspiration du tome : ". $ex->getMessage(), $ex->getCode());
        }
    }

    private function imageToPdf($pdfName) {
        try {
            $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->SetAutoPageBreak(false, 0);
            $numImage = 1;
            $directories = scandir($this->dirDest);
            foreach ($directories as $directory) {
                if ($directory != '.' && $directory != '..') {
                    $images = scandir($this->dirDest . DIRECTORY_SEPARATOR . $directory);
                    foreach ($images as $image) {
                        if ($image != '.' && $image != '..') {
                            $imageInfo = getimagesize(
                                $this->dirDest . DIRECTORY_SEPARATOR . $directory .
                                DIRECTORY_SEPARATOR . $image);
                            $width = $imageInfo[0];
                            $height = $imageInfo[1];
                            if ($width > $height) {
                                $size = array(
                                    297,
                                    210
                                );
                                $pdf->AddPage('L');
                            } else {
                                $size = array(
                                    210,
                                    297
                                );
                                $pdf->AddPage('P');
                            }
                            $pdf->Image(
                                $this->dirDest . DIRECTORY_SEPARATOR . $directory .
                                DIRECTORY_SEPARATOR . $image, 0, 0, $size[0], $size[1],
                                '', '', '', true, 300, '', false, false, 0);
                            $pdf->setPageMark();
                            $numImage ++;
                            $this->serviceMangaDownload->setCurrentPagePdf($numImage);
                        }
                    }
                }
            }
            $pdf->Output($this->dirPdf . DIRECTORY_SEPARATOR . $pdfName, 'F');
            gc_collect_cycles();
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors de la conversion image vers pdf : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Nettoie  le répertoire temporaire source
     *
     * @throws \Exception
     */
    private function cleanSrc() {
        try {
            $elementsToDelete = scandir($this->dirSrc);
            foreach ($elementsToDelete as $elementToDelete) {
                if ($elementToDelete != '.' && $elementToDelete != '..') {
                    if (is_dir($this->dirSrc . DIRECTORY_SEPARATOR . $elementToDelete)) {
                        $dirToDelete = scandir(
                            $this->dirSrc . DIRECTORY_SEPARATOR . $elementToDelete);
                        foreach ($dirToDelete as $fileToDelete) {
                            if ($fileToDelete != '.' && $fileToDelete != '..') {
                                unlink(
                                    $this->dirSrc . DIRECTORY_SEPARATOR . $elementToDelete .
                                    DIRECTORY_SEPARATOR . $fileToDelete);
                            }
                        }
                        rmdir($this->dirSrc . DIRECTORY_SEPARATOR . $elementToDelete);
                    } else {
                        unlink($this->dirSrc . DIRECTORY_SEPARATOR . $elementToDelete);
                    }
                }
            }
            gc_collect_cycles();
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors du nettoyage du répertoire temporaire source : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Nettoie  le répertoire temporaire destination
     *
     * @throws \Exception
     */
    private function cleanDest() {
        try {
            $elementsToDelete = scandir($this->dirDest);
            foreach ($elementsToDelete as $elementToDelete) {
                if ($elementToDelete != '.' && $elementToDelete != '..') {
                    if (is_dir($this->dirDest . DIRECTORY_SEPARATOR . $elementToDelete)) {
                        $dirToDelete = scandir(
                            $this->dirDest . DIRECTORY_SEPARATOR . $elementToDelete);
                        foreach ($dirToDelete as $fileToDelete) {
                            if ($fileToDelete != '.' && $fileToDelete != '..') {
                                unlink(
                                    $this->dirDest . DIRECTORY_SEPARATOR . $elementToDelete .
                                    DIRECTORY_SEPARATOR . $fileToDelete);
                            }
                        }
                        rmdir($this->dirDest . DIRECTORY_SEPARATOR . $elementToDelete);
                    } else {
                        unlink($this->dirDest . DIRECTORY_SEPARATOR . $elementToDelete);
                    }
                }
            }
            gc_collect_cycles();
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors du nettoyage du répertoire temporaire destination : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Contrôle que les répertoires temporaires existent sinon les crées
     *
     * @throws \Exception
     */
    private function checkDirectories() {
        try {
            if (! is_dir($this->dirSrc)) {
                if (! mkdir($this->dirSrc, 0777, true)) {
                    throw new \Exception('Echec lors de la création du répertoire ' . $this->dirSrc);
                }
            }
            if (! is_dir($this->dirDest)) {
                if (! mkdir($this->dirDest, 0777, true)) {
                    throw new \Exception('Echec lors de la création du répertoire ' . $this->dirDest);
                }
            }
            if (! is_dir($this->dirPdf)) {
                if (! mkdir($this->dirPdf, 0777, true)) {
                    throw new \Exception('Echec lors de la création du répertoire ' . $this->dirPdf);
                }
            }
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de contrôle des répertoires temporaires : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Contrôle que les répertoires temporaires du chapitre existent sinon les crées
     *
     * @param MangaChapter $chapter
     * @throws \Exception
     */
    private function checkChapterDirectories(MangaChapter $chapter) {
        try {
            if (! is_dir($this->dirSrc . DIRECTORY_SEPARATOR . $chapter->getId())) {
                if (! mkdir($this->dirSrc . DIRECTORY_SEPARATOR . $chapter->getId(), 0777, true)) {
                    throw new \Exception(
                        'Echec lors de la création du répertoire ' . $this->dirSrc .
                        DIRECTORY_SEPARATOR . $chapter->getId());
                }
            }
            if (! is_dir($this->dirDest . DIRECTORY_SEPARATOR . $chapter->getId())) {
                if (! mkdir($this->dirDest . DIRECTORY_SEPARATOR . $chapter->getId(), 0777, true)) {
                    throw new \Exception(
                        'Echec lors de la création du répertoire ' . $this->dirDest .
                        DIRECTORY_SEPARATOR . $chapter->getId());
                }
            }
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de contrôle des répertoires temporaires du chapitre : ". $ex->getMessage(), $ex->getCode());
        }
    }

    private function getPdfTomeName(MangaTome $tome) {
        try {
            return str_replace(
                array(
                    ' ',
                    '"',
                    ':',
                    '/',
                    '?'
                ),
                array(
                    '_',
                    '',
                    '_',
                    '.',
                    '.'
                ), $tome->getManga()->getTitle() . '_' . $tome->getTitle()) . ".pdf";
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors de la génération du nom du pdf : ". $ex->getMessage(), $ex->getCode());
        }
    }


}