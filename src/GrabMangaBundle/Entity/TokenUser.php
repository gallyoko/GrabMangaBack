<?php

namespace GrabMangaBundle\Entity;

/**
 * TokenUser
 */
class TokenUser
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $value;

    /**
     * @var integer
     */
    private $time;

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
     * Set value
     *
     * @param string $value
     *
     * @return TokenUser
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set time
     *
     * @param integer $time
     *
     * @return TokenUser
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return integer
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set user
     *
     * @param \GrabMangaBundle\Entity\User $user
     *
     * @return TokenUser
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

