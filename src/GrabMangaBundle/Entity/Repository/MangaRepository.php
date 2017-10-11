<?php

namespace GrabMangaBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class MangaRepository extends EntityRepository {

    public function findByTitle($title) {
        $dql = "Select m
 		FROM GrabMangaBundle:Manga m 
		WHERE LOWER(m.title) LIKE :title";
        $query = $this->getEntityManager()->createQuery($dql);
        $titleToClean = strtolower('%'.str_replace([' ', '%20'],'%', $title).'%');
        $query->setParameter("title", $titleToClean);

        return $query->getResult();
    }

    public function findByTitleBeginBy($word) {
        $dql = "Select m
 		FROM GrabMangaBundle:Manga m 
		WHERE LOWER(m.title) LIKE :word 
		ORDER BY m.title ASC";
        $query = $this->getEntityManager()->createQuery($dql);
        $wordToClean = strtolower(str_replace([' ', '%20'],'%', $word).'%');
        $query->setParameter("word", $wordToClean);

        return $query->getResult();
    }
}