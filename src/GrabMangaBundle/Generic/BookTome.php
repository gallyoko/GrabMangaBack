<?php

namespace GrabMangaBundle\Generic;

/**
 * BookTome
 */
class BookTome
{
    /**
     * @var string
     */
    private $title = null;

    /**
     * @var string
     */
    private $cover = null;

    /**
     * @var \GrabMangaBundle\Generic\BookChapter []
     */
    private $bookChapters = [];


    /**
     * Set title
     *
     * @param string $title
     *
     * @return BookTome
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
     * Set cover
     *
     * @param string $cover
     *
     * @return BookTome
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
     * Set bookChapters
     *
     * @param \GrabMangaBundle\Generic\BookChapter[] $bookChapters
     *
     * @return BookTome
     */
    public function setBookChapters($bookChapters = [])
    {
        $this->bookChapters = $bookChapters;

        return $this;
    }

    /**
     * Get bookChapters
     *
     * @return \GrabMangaBundle\Generic\bookChapter[]
     */
    public function getBookChapters()
    {
        return $this->bookChapters;
    }
}

