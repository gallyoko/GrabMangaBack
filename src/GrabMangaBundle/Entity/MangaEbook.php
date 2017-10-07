<?php

namespace GrabMangaBundle\Entity;

/**
 * MangaEbook
 */
class MangaEbook
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $urlMask;

    /**
     * @var \GrabMangaBundle\Entity\MangaChapter
     */
    private $mangaChapter;


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
     * Set urlMask
     *
     * @param string $urlMask
     *
     * @return MangaEbook
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
     * Set mangaChapter
     *
     * @param \GrabMangaBundle\Entity\MangaChapter $mangaChapter
     *
     * @return MangaEbook
     */
    public function setMangaChapter(\GrabMangaBundle\Entity\MangaChapter $mangaChapter = null)
    {
        $this->mangaChapter = $mangaChapter;

        return $this;
    }

    /**
     * Get mangaChapter
     *
     * @return \GrabMangaBundle\Entity\MangaChapter
     */
    public function getMangaChapter()
    {
        return $this->mangaChapter;
    }
}
