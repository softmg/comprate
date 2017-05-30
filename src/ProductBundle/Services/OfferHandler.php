<?php

namespace ProductBundle\Services;


use ApiBundle\RequestObject\RequestObjectHandler;
use ProductBundle\Entity\Offer;
use ProductBundle\Repository\OfferRepo;
use ProductBundle\RequestObjects\CreateOfferRequest;
use ProductBundle\RequestObjects\GetOneOfferRequest;
use ProductBundle\RequestObjects\UpdateOfferTypeAndSiteRequest;

class OfferHandler
{
    /**
     * @var OfferRepo
     */
    private $repo;

    /**
     * @var RequestObjectHandler
     */
    private $requestObjectHandler;

    public function __construct(OfferRepo $repo, RequestObjectHandler $requestObjectHandler)
    {
        $this->repo = $repo;
        $this->requestObjectHandler = $requestObjectHandler;
    }


    /**
     * @param GetOneOfferRequest $request
     * @param null|array $groups
     * @return null|Offer
     */
    public function getOne(GetOneOfferRequest $request, $groups = null)
    {
        $this->requestObjectHandler->validate($request, true, $groups);

        return $this->repo->getOne($request);
    }


    public function createNew(CreateOfferRequest $request)
    {
        $this->requestObjectHandler->validate($request, true);
        $offer = new Offer($request);

        $this->repo->add($offer);

        return $offer;
    }
}