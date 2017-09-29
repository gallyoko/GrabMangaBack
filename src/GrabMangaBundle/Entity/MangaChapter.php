<?php

namespace GrabMangaBundle\Entity;

/**
 * MangaChapter
 */
class MangaChapter
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
    private $url;

    /**
     * @var \GrabMangaBundle\Entity\MangaTome
     */
    private $mangaTome;

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
     * @return MangaChapter
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
     * @return MangaChapter
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
     * Set mangaTome
     *
     * @param \GrabMangaBundle\Entity\MangaTome $mangaTome
     *
     * @return MangaChapter
     */
    public function setMangaTome(\GrabMangaBundle\Entity\MangaTome $mangaTome = null)
    {
        $this->mangaTome = $mangaTome;

        return $this;
    }

    /**
     * Get mangaTome
     *
     * @return \GrabMangaBundle\Entity\MangaTome
     */
    public function getMangaTome()
    {
        return $this->mangaTome;
    }

    /**
     * Set manga
     *
     * @param \GrabMangaBundle\Entity\Manga $manga
     *
     * @return MangaChapter
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

