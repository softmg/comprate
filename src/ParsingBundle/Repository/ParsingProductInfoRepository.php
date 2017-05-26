<?php

namespace ParsingBundle\Repository;

use ParsingBundle\Entity\ParsingProductInfo;

/**
 * ParsingProductInfoRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ParsingProductInfoRepository extends BaseRepo
{
    /**
     * @param $url
     *
     * @return ParsingProductInfo|null
     */
    public function findByUrl($url) {
        $qb = $this->createQueryBuilder('p');
        $qb->where('LOWER(p.url) = LOWER(:url)');
        $qb->setParameter('url', $url);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findByIdOnSite($idOnSite)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where('p.idOnSite = :idOnSite');
        $qb->setParameter('idOnSite', $idOnSite);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function countByIdsOnSite($ids)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where('p.idOnSite IN(:ids)');
        $qb->setParameter('ids', $ids);
        $qb->select('count(p.idOnSite)');

        return (int)$qb->getQuery()->getSingleScalarResult();
    }
}
