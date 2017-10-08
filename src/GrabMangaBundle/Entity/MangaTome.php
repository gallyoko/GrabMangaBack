<?php

namespace GrabMangaBundle\Entity;

/**
 * MangaTome
 */
class MangaTome
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $cover;

    /**
     * @var \GrabMangaBundle\Entity\Manga
     */
    private $manga;


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
     * Set title
     *
     * @param string $title
     *
     * @return MangaTome
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
     * @return MangaTome
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
     * Set manga
     *
     * @param \GrabMangaBundle\Entity\Manga $manga
     *
     * @return MangaTome
     */
    public function setManga(\GrabMangaBundle\Entity\Manga $manga = null)
    {
        $this->manga = $manga;

        return $this;
    }

    /**
     * Get manga
     *
     * @return \GrabMangaBundle\Entity\Manga
     */
    public function getManga()
    {
        return $this->manga;
    }
}

