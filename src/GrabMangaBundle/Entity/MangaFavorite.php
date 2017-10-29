<?php

namespace GrabMangaBundle\Entity;

/**
 * MangaFavorite
 */
class MangaFavorite
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \GrabMangaBundle\Entity\Manga
     */
    private $manga;

    /**
     * @var \GrabMangaBundle\Entity\User
     */
    private $user;


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
     * Set manga
     *
     * @param \GrabMangaBundle\Entity\Manga $manga
     *
     * @return MangaFavorite
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

    /**
     * Set user
     *
     * @param \GrabMangaBundle\Entity\User $user
     *
     * @return MangaFavorite
     */
    public function setUser(\GrabMangaBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \GrabMangaBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}

