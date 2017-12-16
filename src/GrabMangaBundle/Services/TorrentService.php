<?php

namespace GrabMangaBundle\Services;

use GrabMangaBundle\Generic\Book;
use GrabMangaBundle\Generic\BookTome;
use GrabMangaBundle\Generic\BookChapter;
use GrabMangaBundle\Generic\BookEbook;
use GrabMangaBundle\Generic\BookPage;

class TorrentService {
	
	public function __construct() {}

    public function search($json) {
        try {
            $dataIn = json_decode($json);
            set_time_limit(0);
            $dataOut = [];
            $tvShows = [];
            $dataToFormat = [];
            $urlTorrent = 'http://www.torrents9.pe';
            $urlsResult = $this->getUrlsSearch($urlTorrent.'/search_torrent/'.$dataIn->category.'/'.str_replace(' ', '%20', $dataIn->title).'.html');
            foreach ($urlsResult as $urlResult) {
                try {
                    $flux = file_get_contents($urlResult);
                } catch (\Exception $ex) {
                    $flux = false;
                }
                $partSearchBegin = '<td><i class="fa fa-desktop" style="color:#404040"></i> <a title="Télécharger';
                $partSearchEnd = "</a></td>";
                $explode1 = explode($partSearchBegin, $flux);
                for ( $i=1; $i < count($explode1); $i++) {
                    $explode2 = explode($partSearchEnd, $explode1[$i]);
                    $titleTmp = explode(' en Torrent" href="', $explode2[0]);
                    $title = trim($titleTmp[0]);
                    if ( strripos($title, ' S0')!==false
                        || strripos($title, ' S1')!==false
                        || strripos($title, ' S2')!==false) {
                        if ( strripos($title, ' S0')!==false ) {
                            $serie = substr($title, 0, strripos($title, ' S0'));
                        } elseif ( strripos($title, ' S1')!==false ) {
                            $serie = substr($title, 0, strripos($title, ' S1'));
                        } else {
                            $serie = substr($title, 0, strripos($title, ' S2'));
                        }
                    } else {
                        $serie = $title;
                    }
                    if ( ! in_array($serie, $tvShows) ) {
                        $tvShows[] = $serie;
                        $dataToFormat[$serie] = [];
                    }
                    $urlTmp1 = explode('href="', $explode2[0]);
                    $urlTmp2 = explode('" style="color:#000;', $urlTmp1[1]);
                    $url = $urlTorrent.str_replace('/torrent/', '/get_torrent/', trim($urlTmp2[0])).'.torrent';

                    $sizeTmp = explode('</td>', $explode2[1]);
                    $size = str_replace(['<td style="font-size:12px">', "\n"], '', $sizeTmp[0]);

                    $seedTmp1 = explode('<span class="seed_ok">', $explode2[1]);
                    $seedTmp2 = explode('<img class="hidden-md" src="/up.png">', $seedTmp1[1]);
                    $seed = trim($seedTmp2[0]);

                    $torrent = [
                        "title" => $title,
                        "url" => $url,
                        "size" => $size,
                        "seed" => $seed,
                    ];
                    $dataToFormat[$serie][] = $torrent;
                }
            }
            foreach ($dataToFormat as $tvShow => $episode) {
                $dataOut[] = [
                    'tvShow' => $tvShow,
                    'episodes' => $episode,
                ];
            }
            return $dataOut;
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de recherche torrent " .
                "(".__METHOD__." ligne".$ex->getLine().") :" .
                $ex->getMessage(), $ex->getCode());
        }
    }

    private function getUrlsSearch($url) {
        try {
            $urlsPagination = [$url];
            try {
                $flux = file_get_contents($url);
            } catch (\Exception $ex) {
                $flux = false;
            }
            if ($flux !== false) {
                if (strripos($flux, '<div id="pagination-mian"><ul class="pagination">') !== false) {
                    $paginationTemp = explode('<div id="pagination-mian"><ul class="pagination">', $flux);
                    $blockPaginationTemp = explode('<strong>Suiv</strong></a></li>', $paginationTemp[1]);
                    $urlPaginationTemp = explode('<li><a href="', $blockPaginationTemp[0]);
                    $i=0;
                    foreach ($urlPaginationTemp as $urlPagination) {
                        if ($i > 0) {
                            $urlTemp = explode('">', $urlPagination);
                            $urlsPagination[] = $urlTemp[0];
                        }
                        $i++;
                    }
                    return $urlsPagination;
                }
            }
            return array_unique($urlsPagination);
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération des urls de résultats de recherche " .
                "(".__METHOD__." ligne".$ex->getLine().") :" .
                $ex->getMessage(), $ex->getCode());
        }
    }

    public function getCategories() {
        try {
            return [
                "films", "series", "musique", "ebook", "logiciels", "jeux-pc", "jeux-console"
            ];
        } catch (\Exception $ex) {
            throw new \Exception("Erreur de récupération des catégories " .
                "(".__METHOD__." ligne".$ex->getLine().") :" .
                $ex->getMessage(), $ex->getCode());
        }
    }
}