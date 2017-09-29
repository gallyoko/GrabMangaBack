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
    private $listPage;

    /**
     * @var string
     */
    private $listFormat;


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
     * Set listPage
     *
     * @param string $listPage
     *
     * @return BookEbook
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
     * @return BookEbook
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

}

