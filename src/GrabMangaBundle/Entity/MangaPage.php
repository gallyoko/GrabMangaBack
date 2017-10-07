<?php

namespace GrabMangaBundle\Entity;

/**
 * MangaPage
 */
class MangaPage
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $page;

    /**
     * @var \GrabMangaBundle\Entity\MangaEbook
     */
    private $mangaEbook;


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
     * Set page
     *
     * @param string $page
     *
     * @return MangaEbook
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

    /**
     * Set mangaEbook
     *
     * @param \GrabMangaBundle\Entity\MangaEbook $mangaEbook
     *
     * @return MangaPage
     */
    public function setMangaEbook(\GrabMangaBundle\Entity\MangaEbook $mangaEbook = null)
    {
        $this->mangaEbook = $mangaEbook;

        return $this;
    }

    /**
     * Get mangaEbook
     *
     * @return \GrabMangaBundle\Entity\MangaEbook
     */
    public function getMangaEbook()
    {
        return $this->mangaEbook;
    }
}
