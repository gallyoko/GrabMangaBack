<?php

namespace GrabMangaBundle\Services;

use GrabMangaBundle\Generic\Book;
use GrabMangaBundle\Generic\BookTome;
use GrabMangaBundle\Generic\BookChapter;
use GrabMangaBundle\Generic\BookEbook;

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
    public function setTomeAndChapter(Book $book) {
        try {
            $tomes = [];
            try {
                $flux = file_get_contents($book->getUrl());
            } catch (\Exception $ex) {
                $flux = false;
            }
            if ($flux !== false) {
                $partSearchBegin = '<h2 class="bg-header">Liste Des Chapitres</h2>' . "\n" .
                    '<div id="liste_chapitres">';
                list ($partSynopis, $partChapterAndTomeList) = explode($partSearchBegin, $flux);
                // synopsys
                if (strstr($partSynopis, '<div id="synopsis">') !== false) {
                    list ($drop, $synopsisToClean) = explode('<div id="synopsis">', $partSynopis);
                    $synopsis = str_replace('"', '', trim(str_replace('</div>', '', $synopsisToClean)));
                    $book->setSynopsis($synopsis);
                }
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
                                    $ebook = $this->getEbook($chapter);
                                    if ($ebook) {
                                        $chapter->setBookEbook($ebook);
                                        $chapters[] = $chapter;
                                    }
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
            $book->setBookTomes($tomes);
            return $book;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération des tomes et chapitres du manga depuis Japscan " .
                "(".__METHOD__." ligne".$ex->getLine().") :" .
                $ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @param BookChapter $bookChapter
     * @return BookEbook|null
     * @throws \Exception
     */
    private function getEbook(BookChapter $bookChapter) {
        try {
            $bookEbook = null;
            try {
                $fluxToClean = file_get_contents($bookChapter->getUrl());
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
                    $listPage = [];
                    $listFormat = [];
                    foreach ($pageList as $pageToClean) {
                        $pageInfo = explode('" value="', trim($pageToClean));
                        if (count($pageInfo) > 1) {
                            list ($page) = $pageInfo;
                            if ($nbPage == 0) {
                                $pageMinFind = $page;
                            }
                            $pageMaxFind = $page;
                            $infoPage = new \SplFileInfo($page);
                            $extension = $infoPage->getExtension();
                            if ( ! in_array($extension, $listFormat) ) {
                                $listFormat[] = $extension;
                            }
                            $listPage[] = str_replace('.'.$extension, '', $page);
                            $nbPage ++;
                        }
                    }
                    if ($nbPage > 0) {
                        $format = substr(strrchr($pageMinFind, '.'), 1);

                        $pageMaskTemp = str_replace('.' . $format, '.' . '__FORMAT__', $pageMaxFind);
                        $pageMask = str_replace(str_replace('.' . $format, '', $pageMaxFind) . '.', '__PAGE__' . '.', $pageMaskTemp);

                        if (strstr($pageMask, '__FORMAT__') !== false) {
                            if (strstr($pageMask, '__PAGE__') !== false) {
                                list ($drop, $baseUrlToClean) = explode(
                                    '<select name="mangas" id="mangas" ', trim($partBaseUrl));
                                list ($dataUrlNomToClean, $drop, $dataUrlTomeToClean) = explode(
                                    '" data-uri="', trim($baseUrlToClean));
                                $dataUrlNom = trim(str_replace('data-nom="', '', trim($dataUrlNomToClean)));
                                if (strstr($dataUrlTomeToClean, '" data-nom="') !== false) {
                                    list ($drop, $dataUrlTomeToCleanTmp) = explode('" data-nom="',
                                        trim($dataUrlTomeToClean));
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
                                    ->setListPage($listPage)
                                    ->setListFormat($listFormat);
                            }
                        }
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

}