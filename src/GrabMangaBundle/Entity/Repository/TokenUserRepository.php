<?php

namespace GrabMangaBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class TokenUserRepository extends EntityRepository {

    public function getTokenUserToDelete($oldTime) {
        $dql = "Select tu
 		FROM GrabMangaBundle:TokenUser tu 
		WHERE tu.time < :oldTime";
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter("oldTime", $oldTime);

        return $query->getResult();
    }
}