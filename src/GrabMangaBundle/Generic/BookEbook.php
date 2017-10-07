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
     * @var \GrabMangaBundle\Generic\BookPage []
     */
    private $bookPages = [];


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
     * Set bookPages
     *
     * @param \GrabMangaBundle\Generic\BookPage[] $bookPages
     *
     * @return BookEbook
     */
    public function setBookPages($bookPages = [])
    {
        $this->bookPages = $bookPages;

        return $this;
    }

    /**
     * Get bookPages
     *
     * @return \GrabMangaBundle\Generic\BookPage[]
     */
    public function getBookPages()
    {
        return $this->bookPages;
    }
}

