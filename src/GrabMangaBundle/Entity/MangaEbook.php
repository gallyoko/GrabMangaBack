<?php

namespace GrabMangaBundle\Entity;

/**
 * MangaEbook
 */
class MangaEbook
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $urlMask;

    /**
     * @var string
     */
    private $listPage;

    /**
     * @var string
     */
    private $listFormat;

    /**
     * @var \GrabMangaBundle\Entity\MangaChapter
     */
    private $mangaChapter;


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
     * Set urlMask
     *
     * @param string $urlMask
     *
     * @return MangaEbook
     */
    public function setUrlMask($urlMask)
    {
        $this->urlMask = $urlMask;

        return $this;
    }

    /**
     * Get urlMask
     *
     * @return string
     */
    public function getUrlMask()
    {
        return $this->urlMask;
    }

    /**
     * Set listPage
     *
     * @param string $listPage
     *
     * @return MangaEbook
     */
    public function setListPage($listPage)
    {
        $this->listPage = $listPage;

        return $this;
    }

    /**
     * Get listPage
     *
     * @return string
     */
    public function getListPage()
    {
        return $this->listPage;
    }

    /**
     * Set listFormat
     *
     * @param string $listFormat
     *
     * @return MangaEbook
     */
    public function setListFormat($listFormat)
    {
        $this->listFormat = $listFormat;

        return $this;
    }

    /**
     * Get listFormat
     *
     * @return string
     */
    public function getListFormat()
    {
        return $this->listFormat;
    }

    /**
     * Set mangaChapter
     *
     * @param \GrabMangaBundle\Entity\MangaChapter $mangaChapter
     *
     * @return MangaEbook
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
}

