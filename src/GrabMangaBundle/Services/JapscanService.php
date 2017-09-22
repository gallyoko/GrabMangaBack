<?php

namespace GrabMangaBundle\Services;

use GrabMangaBundle\Generic\Book;
use GrabMangaBundle\Generic\BookTome;
use GrabMangaBundle\Generic\BookChapter;

class JapscanService {
	
	public function __construct() {}

    /**
     * Retourne un tableau de livre Book manga contenant titre et url
     *
     * @return array Book
     * @throws \Exception
     */
    public function setMangaTitles() {
        try {
            set_time_limit(0);
            $data = [];
            $flux = file_get_contents('http://www.japscan.com/mangas/');
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
            return $data;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération des titres depuis Japscan : ". $ex->getMessage(), $ex->getCode());
        }
    }

    public function setTomeAndChapter(Book $book) {
        try {
            $tomes = [];
            $flux = file_get_contents($book->getUrl());
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
                                $chapters[] = $chapter;
                            }
                        }
                    }
                }
                $tome->setBookChapters($chapters);
                $tomes[] = $tome;
            }
            $book->setBookTomes($tomes);
            return $book;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération des tomes et chapitres du manga depuis Japscan : ". $ex->getMessage(), $ex->getCode());
        }
    }

}