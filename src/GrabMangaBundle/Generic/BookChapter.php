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
}

