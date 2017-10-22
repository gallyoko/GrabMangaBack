<?php

namespace GrabMangaBundle\Entity;

/**
 * MangaDownload
 */
class MangaDownload
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $currentPageDecode = 0;

    /**
     * @var integer
     */
    private $currentPagePdf = 0;

    /**
     * @var integer
     */
    private $maxPage = 0;

    /**
     * @var integer
     */
    private $currentFileZip = 0;

    /**
     * @var integer
     */
    private $maxFileZip = 0;

    /**
     * @var boolean
     */
    private $current = false;

    /**
     * @var boolean
     */
    private $finished = false;

    /**
     * @var integer
     */
    private $filesize = 0;

    /**
     * @var \GrabMangaBundle\Entity\Manga
     */
    private $manga;

    /**
     * @var \GrabMangaBundle\Entity\MangaChapter
     */
    private $mangaChapter;

    /**
     * @var \GrabMangaBundle\Entity\MangaTome
     */
    private $mangaTome;

    /**
     * @var \GrabMangaBundle\Entity\User
     */
    private $user;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set currentPageDecode
     *
     * @param integer $currentPageDecode
     *
     * @return MangaDownload
     */
    public function setCurrentPageDecode($currentPageDecode)
    {
        $this->currentPageDecode = $currentPageDecode;

        return $this;
    }

    /**
     * Get currentPageDecode
     *
     * @return integer
     */
    public function getCurrentPageDecode()
    {
        return $this->currentPageDecode;
    }

    /**
     * Set currentPagePdf
     *
     * @param integer $currentPagePdf
     *
     * @return MangaDownload
     */
    public function setCurrentPagePdf($currentPagePdf)
    {
        $this->currentPagePdf = $currentPagePdf;

        return $this;
    }

    /**
     * Get currentPagePdf
     *
     * @return integer
     */
    public function getCurrentPagePdf()
    {
        return $this->currentPagePdf;
    }

    /**
     * Set maxPage
     *
     * @param integer $maxPage
     *
     * @return MangaDownload
     */
    public function setMaxPage($maxPage)
    {
        $this->maxPage = $maxPage;

        return $this;
    }

    /**
     * Get maxPage
     *
     * @return integer
     */
    public function getMaxPage()
    {
        return $this->maxPage;
    }

    /**
     * Set currentFileZip
     *
     * @param integer $currentFileZip
     *
     * @return MangaDownload
     */
    public function setCurrentFileZip($currentFileZip)
    {
        $this->currentFileZip = $currentFileZip;

        return $this;
    }

    /**
     * Get currentFileZip
     *
     * @return integer
     */
    public function getCurrentFileZip()
    {
        return $this->currentFileZip;
    }

    /**
     * Set maxFileZip
     *
     * @param integer $maxFileZip
     *
     * @return MangaDownload
     */
    public function setMaxFileZip($maxFileZip)
    {
        $this->maxFileZip = $maxFileZip;

        return $this;
    }

    /**
     * Get maxFileZip
     *
     * @return integer
     */
    public function getMaxFileZip()
    {
        return $this->maxFileZip;
    }

    /**
     * Set current
     *
     * @param boolean $current
     *
     * @return MangaDownload
     */
    public function setCurrent($current)
    {
        $this->current = $current;

        return $this;
    }

    /**
     * Get current
     *
     * @return boolean
     */
    public function getCurrent()
    {
        return $this->current;
    }

    /**
     * Set finished
     *
     * @param boolean $finished
     *
     * @return MangaDownload
     */
    public function setFinished($finished)
    {
        $this->finished = $finished;

        return $this;
    }

    /**
     * Get finished
     *
     * @return boolean
     */
    public function getFinished()
    {
        return $this->finished;
    }

    /**
     * Set filesize
     *
     * @param integer $filesize
     *
     * @return MangaDownload
     */
    public function setFilesize($filesize)
    {
        $this->filesize = $filesize;

        return $this;
    }

    /**
     * Get filesize
     *
     * @return integer
     */
    public function getFilesize()
    {
        return $this->filesize;
    }

    /**
     * Set manga
     *
     * @param \GrabMangaBundle\Entity\Manga $manga
     *
     * @return MangaDownload
     */
    public function setManga(\GrabMangaBundle\Entity\Manga $manga = null)
    {
        $this->manga = $manga;

        return $this;
    }

    /**
     * Get manga
     *
     * @return \GrabMangaBundle\Entity\Manga
     */
    public function getManga()
    {
        return $this->manga;
    }

    /**
     * Set mangaChapter
     *
     * @param \GrabMangaBundle\Entity\MangaChapter $mangaChapter
     *
     * @return MangaDownload
     */
    public function setMangaChapter(\GrabMangaBundle\Entity\MangaChapter $mangaChapter = null)
    {
        $this->mangaChapter = $mangaChapter;

        return $this;
    }

    /**
     * Get mangaChapter
     *
     * @return \GrabMangaBundle\Entity\MangaChapter
     */
    public function getMangaChapter()
    {
        return $this->mangaChapter;
    }

    /**
     * Set mangaTome
     *
     * @param \GrabMangaBundle\Entity\MangaTome $mangaTome
     *
     * @return MangaDownload
     */
    public function setMangaTome(\GrabMangaBundle\Entity\MangaTome $mangaTome = null)
    {
        $this->mangaTome = $mangaTome;

        return $this;
    }

    /**
     * Get mangaTome
     *
     * @return \GrabMangaBundle\Entity\MangaTome
     */
    public function getMangaTome()
    {
        return $this->mangaTome;
    }

    /**
     * Set user
     *
     * @param \GrabMangaBundle\Entity\User $user
     *
     * @return MangaDownload
     */
    public function setUser(\GrabMangaBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \GrabMangaBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}

