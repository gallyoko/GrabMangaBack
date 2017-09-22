<?php

namespace GrabMangaBundle\Generic;

/**
 * Book
 */
class Book
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
    private $synopsis;

    /**
     * @var \GrabMangaBundle\Generic\BookTome []
     */
    private $bookTomes = [];


    /**
     * Set title
     *
     * @param string $title
     *
     * @return Book
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
     * @return Book
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
     * Set synopsis
     *
     * @param string $synopsis
     *
     * @return Book
     */
    public function setSynopsis($synopsis)
    {
        $this->synopsis = $synopsis;

        return $this;
    }

    /**
     * Get synopsis
     *
     * @return string
     */
    public function getSynopsis()
    {
        return $this->synopsis;
    }

    /**
     * Set bookTomes
     *
     * @param \GrabMangaBundle\Generic\BookTome[] $bookTomes
     *
     * @return Book
     */
    public function setBookTomes($bookTomes = [])
    {
        $this->bookTomes = $bookTomes;

        return $this;
    }

    /**
     * Get bookTomes
     *
     * @return \GrabMangaBundle\Generic\BookTome[]
     */
    public function getBookTomes()
    {
        return $this->bookTomes;
    }
}

