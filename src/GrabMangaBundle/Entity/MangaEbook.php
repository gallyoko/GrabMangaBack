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
    private $pageMin;

    /**
     * @var string
     */
    private $pageMax;

    /**
     * @var string
     */
    private $pageMask;

    /**
     * @var string
     */
    private $format;

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
     * Set pageMin
     *
     * @param string $pageMin
     *
     * @return MangaEbook
     */
    public function setPageMin($pageMin)
    {
        $this->pageMin = $pageMin;

        return $this;
    }

    /**
     * Get pageMin
     *
     * @return string
     */
    public function getPageMin()
    {
        return $this->pageMin;
    }

    /**
     * Set pageMax
     *
     * @param string $pageMax
     *
     * @return MangaEbook
     */
    public function setPageMax($pageMax)
    {
        $this->pageMax = $pageMax;

        return $this;
    }

    /**
     * Get pageMax
     *
     * @return string
     */
    public function getPageMax()
    {
        return $this->pageMax;
    }

    /**
     * Set pageMask
     *
     * @param string $pageMask
     *
     * @return MangaEbook
     */
    public function setPageMask($pageMask)
    {
        $this->pageMask = $pageMask;

        return $this;
    }

    /**
     * Get pageMask
     *
     * @return string
     */
    public function getPageMask()
    {
        return $this->pageMask;
    }

    /**
     * Set format
     *
     * @param string $format
     *
     * @return MangaEbook
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Get format
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
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

