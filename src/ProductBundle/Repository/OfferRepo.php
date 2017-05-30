<?php

namespace ProductBundle\Repository;


use ParsingBundle\Entity\ParsingProductInfo;
use ParsingBundle\Entity\ParsingSite;
use ProductBundle\Entity\Offer;
use ProductBundle\RequestObjects\GetOneOfferRequest;

class OfferRepo extends BaseRepo
{
    /**
     * @param $url
     *
     * @return ParsingProductInfo|null
     */
    public function findByUrl($url) {
        $qb = $this->createQueryBuilder('o');
        $qb->where('LOWER(o.productInfo.url) = LOWER(:url)');
        $qb->setParameter('url', $url);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findByIdOnSite($idOnSite)
    {
        $qb = $this->createQueryBuilder('o');
        $qb->where('o.productInfo.idOnSite = :idOnSite');
        $qb->setParameter('idOnSite', $idOnSite);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function countByIdsOnSite($ids)
    {
        $qb = $this->createQueryBuilder('o');
        $qb->where('o.productInfo.idOnSite IN(:ids)');
        $qb->setParameter('ids', $ids);
        $qb->select('count(o.productInfo.idOnSite)');

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    public function unhandledOffersQB(ParsingSite $site)
    {
        $qb = $this->createQueryBuilder('o');

        $qb->andWhere('o.site = :site');

        $qb->andWhere('o.productInfo.isFail = true OR o.productInfo.createdAt > :createdAt');

        $qb->setParameters([
            'site' => $site,
            'createdAt' => new \DateTime('-1 week'),
        ]);

        return $qb;
    }

    /**
     * @param GetOneOfferRequest $request
     *
     * @return Offer|null
     */
    public function getOne(GetOneOfferRequest $request): ?Offer
    {
        $qb = $this->createQueryBuilder('o');

        if (null !== $request->idOnSite) {
            $qb->andWhere('o.productInfo.idOnSite = :idOnSite');
            $qb->setParameter('idOnSite', $request->idOnSite);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }
}