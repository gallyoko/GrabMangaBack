<?php

namespace GrabMangaBundle\Generic;

/**
 * BookChapter
 */
class BookChapter
{

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $cover;

    /**
     * @var \GrabMangaBundle\Generic\BookEbook
     */
    private $bookEbook = null;


    /**
     * Set title
     *
     * @param string $title
     *
     * @return BookChapter
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return BookChapter
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set cover
     *
     * @param string $cover
     *
     * @return BookChapter
     */
    public function setCover($cover)
    {
        $this->cover = $cover;

        return $this;
    }

    /**
     * Get cover
     *
     * @return string
     */
    public function getCover()
    {
        return $this->cover;
    }

    /**
     * Set bookEbook
     *
     * @param \GrabMangaBundle\Generic\BookEbook $bookEbook
     *
     * @return BookChapter
     */
    public function setBookEbook(\GrabMangaBundle\Generic\BookEbook $bookEbook = null)
    {
        $this->bookEbook = $bookEbook;

        return $this;
    }

    /**
     * Get bookEbook
     *
     * @return \GrabMangaBundle\Generic\BookEbook
     */
    public function getBookEbook()
    {
        return $this->bookEbook;
    }
}

