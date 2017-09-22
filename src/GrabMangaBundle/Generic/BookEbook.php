<?php

namespace GrabMangaBundle\Generic;

/**
 * BookEbook
 */
class BookEbook
{
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
     * Set urlMask
     *
     * @param string $urlMask
     *
     * @return BookEbook
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
     * @return BookEbook
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
     * @return BookEbook
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
     * @return BookEbook
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
     * @return BookEbook
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

}

