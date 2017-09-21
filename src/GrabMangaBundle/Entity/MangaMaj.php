<?php

namespace GrabMangaBundle\Entity;

/**
 * MangaMaj
 */
class MangaMaj
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $timestamp;

    /**
     * @var integer
     */
    private $countTitle;

    /**
     * @var integer
     */
    private $countTome;

    /**
     * @var integer
     */
    private $countChapter;

    /**
     * @var string
     */
    private $filename;


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
     * Set timestamp
     *
     * @param integer $timestamp
     *
     * @return MangaMaj
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * Get timestamp
     *
     * @return integer
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Set countTitle
     *
     * @param integer $countTitle
     *
     * @return MangaMaj
     */
    public function setCountTitle($countTitle)
    {
        $this->countTitle = $countTitle;

        return $this;
    }

    /**
     * Get countTitle
     *
     * @return integer
     */
    public function getCountTitle()
    {
        return $this->countTitle;
    }

    /**
     * Set countTome
     *
     * @param integer $countTome
     *
     * @return MangaMaj
     */
    public function setCountTome($countTome)
    {
        $this->countTome = $countTome;

        return $this;
    }

    /**
     * Get countTome
     *
     * @return integer
     */
    public function getCountTome()
    {
        return $this->countTome;
    }

    /**
     * Set countChapter
     *
     * @param integer $countChapter
     *
     * @return MangaMaj
     */
    public function setCountChapter($countChapter)
    {
        $this->countChapter = $countChapter;

        return $this;
    }

    /**
     * Get countChapter
     *
     * @return integer
     */
    public function getCountChapter()
    {
        return $this->countChapter;
    }

    /**
     * Set filename
     *
     * @param string $filename
     *
     * @return MangaMaj
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }
}

