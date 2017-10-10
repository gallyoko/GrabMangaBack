<?php

namespace GrabMangaBundle\Services;

use GrabMangaBundle\Generic\Book;
use GrabMangaBundle\Generic\BookTome;
use GrabMangaBundle\Generic\BookChapter;
use GrabMangaBundle\Generic\BookEbook;
use GrabMangaBundle\Generic\BookPage;

class JapscanService {
	
	public function __construct() {}

    /**
     * Retourne un tableau de livre Book manga contenant titre et url
     *
     * @return array Book
     * @throws \Exception
     */
    public function getMangaTitles() {
        try {
            set_time_limit(0);
            $data = [];
            try {
                $flux = file_get_contents('http://www.japscan.com/mangas/');
            } catch (\Exception $ex) {
                $flux = false;
            }
            if ($flux !== false) {
                $partSearchBegin = "<div class=\"row\">\n<div class=\"cell\"><a href=\"";
                $partSearchEnd = "</a></div>\n<div class=\"cell\">";
                $explode1 = explode($partSearchBegin, $flux);
                foreach ($explode1 as $element) {
                    $explode2 = explode($partSearchEnd, $element);
                    $explode3 = explode('">', $explode2[0]);
                    if ((count($explode3) > 0) && (strlen($explode3[1]) < 100)) {
                        $book = new Book();
                        $book->setTitle(trim($explode3[1]))
                            ->setUrl('http://www.japscan.com' .trim($explode3[0]));
                        $data[] = $book;
                    }
                }
            }
            return $data;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération des titres depuis Japscan " .
                "(".__METHOD__." ligne".$ex->getLine().") :" .
                $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @param Book $book
     * @return Book
     * @throws \Exception
     */
    public function setSynopsis(Book $book) {
        try {
            $synopsis = '';
            try {
                $flux = file_get_contents($book->getUrl());
            } catch (\Exception $ex) {
                $flux = false;
            }
            if ($flux !== false) {
                $partSearchBegin = '<h2 class="bg-header">Liste Des Chapitres</h2>' . "\n" .
                    '<div id="liste_chapitres">';
                list ($partSynopis, $drop) = explode($partSearchBegin, $flux);
                unset($drop);
                // synopsis
                if (strstr($partSynopis, '<div id="synopsis">') !== false) {
                    list ($drop, $synopsisToClean) = explode('<div id="synopsis">', $partSynopis);
                    unset($drop);
                    $synopsis = str_replace('"', '', trim(str_replace('</div>', '', $synopsisToClean)));
                }
            }
            $book->setSynopsis($synopsis);
            return $book;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération du synopsis du manga depuis Japscan " .
                "(".__METHOD__." ligne".$ex->getLine().") :" .
                $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @param Book $book
     * @return Book
     * @throws \Exception
     */
    public function setCover(Book $book) {
        try {
            $listCover = $this->getGoogleImage($book->getTitle().' affiche');
            if (count($listCover)>0){
                $book->setCover($listCover[0]);
            }
            return $book;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération de l'affiche du manga " .
                "(".__METHOD__." ligne".$ex->getLine().") :" .
                $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @param $url
     * @return array
     * @throws \Exception
     */
    public function getTomeAndChapter($url) {
        try {
            $tomes = [];
            try {
                $flux = file_get_contents($url);
            } catch (\Exception $ex) {
                $flux = false;
            }
            if ($flux !== false) {
                $partSearchBegin = '<h2 class="bg-header">Liste Des Chapitres</h2>' . "\n" .
                    '<div id="liste_chapitres">';
                list ($drop, $partChapterAndTomeList) = explode($partSearchBegin, $flux);
                unset($drop);
                $partSearchEnd = '</a>' . "\n" . '</li>' . "\n" . '</ul>';
                $chapterAndTomeList = explode($partSearchEnd, $partChapterAndTomeList);
                foreach ($chapterAndTomeList as $chapterAndTome) {
                    $chapters = [];
                    $tome = new BookTome();
                    // tome éventuel
                    if (substr(trim($chapterAndTome), 0, 4) == '<h2>') {
                        list ($tomeTitleToClean) = explode('</h2>', trim($chapterAndTome));
                        $tomeTitleTmp = trim(str_replace('<h2>', '', trim($tomeTitleToClean)));
                        $tomeTitle = trim(str_replace('"', '', trim($tomeTitleTmp)));
                        $tome->setTitle($tomeTitle);
                    }
                    $chapterList = explode('<li>' . "\n" . '<a href="', $chapterAndTome);
                    if (count($chapterList) > 1) {
                        foreach ($chapterList as $chapterToClean) {
                            $urlAndTitleChapterToClean = explode('">', $chapterToClean);
                            if (count($urlAndTitleChapterToClean) > 1) {
                                list ($urlChapterToClean, $titleChapterToClean) = $urlAndTitleChapterToClean;
                                if (strstr($urlChapterToClean, '//www.japscan.com/lecture-en-ligne') !==
                                    false) {
                                    $urlChapter = 'http:' . trim($urlChapterToClean);
                                    $titleChapter = str_replace('"', '',
                                        trim(
                                            trim(
                                                str_replace('</a>' . "\n" . '</li>', '',
                                                    $titleChapterToClean))));
                                    $chapter = new BookChapter();
                                    $chapter->setTitle($titleChapter)
                                        ->setUrl($urlChapter);
                                    $chapters[] = $chapter;
                                }
                            }
                        }
                    }
                    if (count($chapters)>0) {
                        $tome->setBookChapters($chapters);
                        $tomes[] = $tome;
                    }
                }
            }
            return $tomes;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération des tomes et chapitres du manga depuis Japscan " .
                "(".__METHOD__." ligne".$ex->getLine().") :" .
                $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @param $url
     * @return BookEbook|null
     * @throws \Exception
     */
    public function getEbook($url) {
        try {
            $bookEbook = null;
            try {
                $fluxToClean = file_get_contents($url);
            } catch (\Exception $ex) {
                $fluxToClean = false;
            }
            if ($fluxToClean !== false) {
                $flux = str_replace("\n", ' ', $fluxToClean);
                $partSearchBegin = '<select id="pages" name="pages">';
                $checkExplode = explode($partSearchBegin, $flux);
                if (count($checkExplode) > 1) {
                    list ($partBaseUrl, $partPages) = $checkExplode;
                    $partSearchEnd = '</select>';
                    list ($pageListToClean) = explode($partSearchEnd, trim($partPages));
                    $pageList = explode('data-img="', trim($pageListToClean));
                    $nbPage = 0;
                    $bookPages = [];
                    foreach ($pageList as $pageToClean) {
                        $pageInfo = explode('" value="', trim($pageToClean));
                        if (count($pageInfo) > 1) {
                            list ($pageToTrim) = $pageInfo;
                            $page = trim($pageToTrim);
                            if(substr($page, 0, 4) != 'IMG_') {
                                $bookPage = new BookPage();
                                $bookPage->setPage($page);
                                $bookPages[] = $bookPage;
                                $nbPage ++;
                            }
                        }
                    }
                    if ($nbPage > 0) {
                        list ($drop, $baseUrlToClean) = explode(
                            '<select name="mangas" id="mangas" ', trim($partBaseUrl));
                        unset($drop);
                        list ($dataUrlNomToClean, $drop, $dataUrlTomeToClean) = explode(
                            '" data-uri="', trim($baseUrlToClean));
                        $dataUrlNom = trim(str_replace('data-nom="', '', trim($dataUrlNomToClean)));
                        if (strstr($dataUrlTomeToClean, '" data-nom="') !== false) {
                            list ($drop, $dataUrlTomeToCleanTmp) = explode('" data-nom="',
                                trim($dataUrlTomeToClean));
                            unset($drop);
                            $dataUrlTome = trim(
                                str_replace('"></select>', '', trim($dataUrlTomeToCleanTmp)));
                        } else {
                            $dataUrlTome = trim(
                                str_replace('"></select>', '', trim($dataUrlTomeToClean)));
                        }
                        $urlMask = 'http://cdn.japscan.com/lel/' . $dataUrlNom . '/' .
                            $dataUrlTome . '/';
                        $bookEbook = new BookEbook();
                        $bookEbook->setUrlMask($urlMask)
                            ->setBookPages($bookPages);
                    }
                }
            }
            return $bookEbook;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération de l'ebook du chapitre depuis Japscan " .
                "(".__METHOD__." ligne".$ex->getLine().") :" .
                $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @param $keyword
     * @return array
     * @throws \Exception
     */
    private function getGoogleImage($keyword){
        try {
            $file = file_get_contents('https://www.google.fr/search?q='.str_replace(' ', '+', $keyword).'&tbm=isch', FILE_USE_INCLUDE_PATH);
            $imgLinkTemp = explode ('src="https://encrypted-tbn0.gstatic.com/images?q=tbn:', $file);
            unset($imgLinkTemp[count($imgLinkTemp)-1]);
            unset($imgLinkTemp[0]);
            $imgLinks=[];
            foreach ($imgLinkTemp as $linkTemp) {
                $linkTemp = explode('" width="', $linkTemp);
                $imgLinks[] = 'https://encrypted-tbn0.gstatic.com/images?q=tbn:'.$linkTemp[0];
            }
            return $imgLinks;
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage(), 500);
        }
    }
}