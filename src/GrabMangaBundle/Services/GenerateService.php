<?php
namespace GrabMangaBundle\Services;

use GrabMangaBundle\Entity\Manga;
use GrabMangaBundle\Entity\MangaPage;
use GrabMangaBundle\Entity\MangaEbook;
use GrabMangaBundle\Entity\MangaTome;
use GrabMangaBundle\Entity\MangaChapter;
use GrabMangaBundle\Entity\MangaDownload;

/**
 * Class GenerateService
 * @package GrabMangaBundle\Services
 */
class GenerateService {
    private $doctrine;
    private $em;
    private $validator;
    private $serviceMessage;
    private $serviceMangaDownload;
    private $serviceMangaTome;
    private $serviceMangaChapter;
    private $serviceMangaEbook;
    private $dirSrc;
    private $dirDest;
    private $dirPdf;
    private $user;

    /**
     * GenerateService constructor.
     *
     * @param $doctrine
     * @param $validator
     * @param MessageService $serviceMessage
     * @param MangaDownloadService $serviceMangaDownload
     * @param MangaTomeService $serviceMangaTome
     * @param MangaChapterService $serviceMangaChapter
     * @param MangaEbookService $serviceMangaEbook
     * @param $rootDir
     * @param $path
     */
    public function __construct($doctrine, $validator, MessageService $serviceMessage,
                                MangaDownloadService $serviceMangaDownload,
                                MangaTomeService $serviceMangaTome,
                                MangaChapterService $serviceMangaChapter,
                                MangaEbookService $serviceMangaEbook, $rootDir, $path) {
        $this->doctrine = $doctrine;
        $this->em = $doctrine->getManager();
        $this->validator = $validator;
        $this->serviceMangaDownload = $serviceMangaDownload;
        $this->serviceMangaTome = $serviceMangaTome;
        $this->serviceMangaChapter = $serviceMangaChapter;
        $this->serviceMangaEbook = $serviceMangaEbook;
        $this->serviceMessage = $serviceMessage;
        $this->dirSrc = $rootDir.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.
            $path['ebook']['src'];
        $this->dirDest = $rootDir.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.
            $path['ebook']['dst'];
        $this->dirPdf = $rootDir.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.
            $path['ebook']['pdf'];
    }

    /**
     * Génère un book au format pdf et retourne le temps écoulé.
     * Met à jour l'état du téléchargement
     *
     * @param Manga $manga
     * @param MangaDownload $download
     * @return array timeElapsed
     * @throws \Exception
     */
    public function generateByBook(Manga $manga, MangaDownload $download) {
        try {
            $timestampIn = time();
            $this->init($download);
            $tomes = $this->serviceMangaTome->getByManga($manga);
            $pdfFilenames = [];
            foreach ($tomes as $tome) {
                $this->aspireTome($tome);
                $pdfFilename = $this->getPdfTomeName($tome);
                $pdfFilenames[] = $pdfFilename;
                $this->imageToPdf($pdfFilename);
                $this->cleanDirectory($this->dirDest);
            }
            $chapters = $this->serviceMangaChapter->getByMangaWithoutTome($manga);
            foreach ($chapters as $chapter) {
                $this->aspireChapter($chapter);
                $pdfFilename = $this->getPdfChapterName($chapter);
                $pdfFilenames[] = $pdfFilename;
                $this->imageToPdf($pdfFilename);
                $this->cleanDirectory($this->dirDest);
            }
            $this->serviceMangaDownload->setMaxFileZip(count($pdfFilenames));
            $bookFilename = $this->getBookName($manga);
            $this->compressBook($bookFilename);
            $this->cleanPdfDirectory($pdfFilenames);
            $this->serviceMangaDownload->tagFinished();
            gc_collect_cycles();
            $timestamp = time() - $timestampIn;
            return ['timeElapsed' => $timestamp];
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de génération du book : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Génère un tome au format pdf et retourne le temps écoulé.
     * Met à jour l'état du téléchargement
     *
     * @param MangaTome $tome
     * @param MangaDownload $download
     * @return array timeElapsed
     * @throws \Exception
     */
    public function generateByTome(MangaTome $tome, MangaDownload $download) {
        try {
            $timestampIn = time();
            $this->init($download);
            $this->aspireTome($tome);
            $pdfFilename = $this->getPdfTomeName($tome);
            $this->imageToPdf($pdfFilename);
            $this->cleanDirectory($this->dirDest);
            $this->serviceMangaDownload->tagFinished();
            gc_collect_cycles();
            $timestamp = time() - $timestampIn;
            return ['timeElapsed' => $timestamp];
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de génération du tome : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Génère un chapitre au format pdf et retourne le temps écoulé.
     * Met à jour l'état du téléchargement
     *
     * @param MangaChapter $chapter
     * @param MangaDownload $download
     * @return array timeElapsed
     * @throws \Exception
     */
    public function generateByChapter(MangaChapter $chapter, MangaDownload $download) {
        try {
            $timestampIn = time();
            $this->init($download);
            $this->aspireChapter($chapter);
            $pdfFilename = $this->getPdfChapterName($chapter);
            $this->imageToPdf($pdfFilename);
            $this->cleanDirectory($this->dirDest);
            $this->serviceMangaDownload->tagFinished();
            gc_collect_cycles();
            $timestamp = time() - $timestampIn;
            return ['timeElapsed' => $timestamp];
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de génération du chapitre : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Tag le lancement de la génération en base et initialise les dossiers de traitement
     *
     * @param MangaDownload $download
     * @throws \Exception
     */
    private function init(MangaDownload $download) {
        try {
            set_time_limit(0);
            $this->user = $download->getUser();
            $this->serviceMangaDownload->setMangaDownload($download);
            $this->checkDirectories();
            $this->cleanDirectory($this->dirSrc);
            $this->serviceMangaDownload->tagCurrent();
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors de l'initialisation de la génération : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Télécharge les images du tome
     * Met à jour l'état du téléchargement
     *
     * @param MangaTome $tome
     * @throws \Exception
     */
    private function aspireTome(MangaTome $tome) {
        try {
            $chapters = $this->serviceMangaChapter->getByTome($tome);
            foreach ($chapters as $chapter) {
                $this->aspireChapter($chapter);
            }
            gc_collect_cycles();
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors de l'aspiration du tome : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Télécharge les images du chapitre
     * Met à jour l'état du téléchargement
     *
     * @param MangaChapter $chapter
     * @throws \Exception
     */
    private function aspireChapter(MangaChapter $chapter) {
        try {
            $mangaEbook = $this->serviceMangaChapter->getEbook($chapter);
            if ($mangaEbook) {
                $this->checkChapterDirectories($chapter);
                $numPageDecode = $this->serviceMangaDownload->getCurrentPageDecode();
                $mangaPages = $this->serviceMangaEbook->getMangaPages($mangaEbook);
                foreach ($mangaPages as $mangaPage) {
                    $this->savePageImage($mangaEbook, $mangaPage);
                    $numPageDecode ++;
                    $this->serviceMangaDownload->setCurrentPageDecode($numPageDecode);
                }
                rmdir($this->dirSrc . DIRECTORY_SEPARATOR . $chapter->getId());
            }
            gc_collect_cycles();
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors de l'aspiration du chapitre : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * récupère et sauvegarde l'image d'une page depuis l'url d'un ebook
     *
     * @param MangaEbook $mangaEbook
     * @param MangaPage $mangaPage
     */
    private function savePageImage(MangaEbook $mangaEbook, MangaPage $mangaPage) {
        try {
            $url = str_replace(' ', '%20',$mangaEbook->getUrlMask()).$mangaPage->getPage();
            $page = str_replace(['.png', '.PNG', '.gif', '.GIF', '.JPG'], '.jpg', $mangaPage->getPage());
            $fileTmp = $this->dirSrc . DIRECTORY_SEPARATOR . $mangaEbook->getMangaChapter()->getId() .
                DIRECTORY_SEPARATOR . $page;
            $fileEnd = $this->dirDest . DIRECTORY_SEPARATOR . $mangaEbook->getMangaChapter()->getId() .
                DIRECTORY_SEPARATOR . $page;
            $current = imagecreatefromjpeg($url);
            if ($current) {
                imagejpeg($current, $fileTmp);
                imagedestroy($current);
                copy($fileTmp, $fileEnd);
                unlink($fileTmp);
            }
        } catch (\Exception $ex) {
            //throw new \Exception("Erreur lors de l'enregistrement de l'image : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Conversion de l'ensemble des images du répertoire de destination en un fichier pdf
     * Met à jour l'état du téléchargement
     *
     * @param $pdfName
     * @throws \Exception
     */
    private function imageToPdf($pdfName) {
        try {
            $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->SetAutoPageBreak(false, 0);
            $numImage = $this->serviceMangaDownload->getCurrentPagePdf();
            $directories = scandir($this->dirDest, SCANDIR_SORT_DESCENDING);
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
     * Nettoie  le répertoire
     *
     * @param $directory
     * @throws \Exception
     */
    private function cleanDirectory($directory) {
        try {
            $elementsToDelete = scandir($directory);
            foreach ($elementsToDelete as $elementToDelete) {
                if ($elementToDelete != '.' && $elementToDelete != '..') {
                    if (is_dir($directory . DIRECTORY_SEPARATOR . $elementToDelete)) {
                        $dirToDelete = scandir(
                            $directory . DIRECTORY_SEPARATOR . $elementToDelete);
                        foreach ($dirToDelete as $fileToDelete) {
                            if ($fileToDelete != '.' && $fileToDelete != '..') {
                                unlink(
                                    $directory . DIRECTORY_SEPARATOR . $elementToDelete .
                                    DIRECTORY_SEPARATOR . $fileToDelete);
                            }
                        }
                        rmdir($directory . DIRECTORY_SEPARATOR . $elementToDelete);
                    } else {
                        unlink($directory . DIRECTORY_SEPARATOR . $elementToDelete);
                    }
                }
            }
            gc_collect_cycles();
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors du nettoyage du répertoire : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Nettoie  le répertoire PDF pour les pdf fournis
     *
     * @param array $pdfFilenames
     * @throws \Exception
     */
    private function cleanPdfDirectory($pdfFilenames) {
        try {
            foreach ($pdfFilenames as $pdfFilename) {
                unlink(
                    $this->dirPdf . DIRECTORY_SEPARATOR . $pdfFilename);
            }
            gc_collect_cycles();
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors du nettoyage du répertoire : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Contrôle que les répertoires temporaires existent sinon les crées
     *
     * @throws \Exception
     */
    private function checkDirectories() {
        try {
            $this->dirSrc = $this->dirSrc.DIRECTORY_SEPARATOR.$this->user->getId();
            $this->dirDest = $this->dirDest.DIRECTORY_SEPARATOR.$this->user->getId();
            $this->dirPdf = $this->dirPdf.DIRECTORY_SEPARATOR.$this->user->getId();

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

    /**
     * Retourne le nom du fichier pdf pour un tome
     *
     * @param MangaTome $tome
     * @return string
     * @throws \Exception
     */
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
            throw new \Exception("Erreur lors de la génération du nom du pdf du tome : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Retourne le nom du fichier pdf pour un chapitre
     *
     * @param MangaChapter $chapter
     * @return string
     * @throws \Exception
     */
    private function getPdfChapterName(MangaChapter $chapter) {
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
                    ), $chapter->getManga()->getTitle() . '_' . $chapter->getTitle()) . ".pdf";
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors de la génération du nom du pdf du chapitre : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Retourne le nom du fichier pour un manga
     *
     * @param Manga $manga
     * @return string
     * @throws \Exception
     */
    private function getBookName(Manga $manga) {
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
                    ), $manga->getTitle());
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors de la génération du nom du pdf du manga : ". $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Compresse le contenu du répertoire PDF au format zip
     *
     * @param $bookFilename
     * @throws \Exception
     */
    private function compressBook($bookFilename) {
        try {
            $zip = new \ZipArchive;
            $realPathPdf = realpath($this->dirPdf);
            if ($zip->open($realPathPdf . DIRECTORY_SEPARATOR . $bookFilename . '.zip', \ZIPARCHIVE::CREATE | \ZIPARCHIVE::OVERWRITE) === FALSE) {
                throw new \Exception("Erreur lors de la création du book compressé", 500);
            }
            $numFileZip = 0;
            $elementsToCompress = scandir($realPathPdf);
            foreach ($elementsToCompress as $elementToCompress) {
                if ($elementToCompress != '.' && $elementToCompress != '..' && $elementToCompress != $bookFilename . '.zip') {
                    $zip->addFile($realPathPdf . DIRECTORY_SEPARATOR . $elementToCompress, basename($elementToCompress));
                    $numFileZip++;
                    $this->serviceMangaDownload->setCurrentFileZip($numFileZip);
                }
            }
            $zip->close();
        } catch (\Exception $ex) {
            throw new \Exception("Erreur lors de la compression du book : ". $ex->getMessage(), $ex->getCode());
        }
    }
}