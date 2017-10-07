<?php

namespace GrabMangaBundle\Generic;

/**
 * BookPage
 */
class BookPage
{
    /**
     * @var string
     */
    private $page;



    /**
     * Set page
     *
     * @param string $page
     *
     * @return BookPage
     */
    public function setPage($page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get page
     *
     * @return string
     */
    public function getPage()
    {
        return $this->page;
    }

}

