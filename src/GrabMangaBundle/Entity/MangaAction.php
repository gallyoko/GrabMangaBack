<?php

namespace GrabMangaBundle\Entity;

/**
 * MangaAction
 */
class MangaAction
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $save = '0';

    /**
     * @var boolean
     */
    private $maj = '0';


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
     * Set save
     *
     * @param boolean $save
     *
     * @return MangaAction
     */
    public function setSave($save)
    {
        $this->save = $save;

        return $this;
    }

    /**
     * Get save
     *
     * @return boolean
     */
    public function getSave()
    {
        return $this->save;
    }

    /**
     * Set maj
     *
     * @param boolean $maj
     *
     * @return MangaAction
     */
    public function setMaj($maj)
    {
        $this->maj = $maj;

        return $this;
    }

    /**
     * Get maj
     *
     * @return boolean
     */
    public function getMaj()
    {
        return $this->maj;
    }
}
