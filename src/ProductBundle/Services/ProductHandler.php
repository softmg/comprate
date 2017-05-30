<?php

namespace ProductBundle\Services;


use ApiBundle\RequestObject\ProductInfoRequest;
use ApiBundle\RequestObject\RequestObjectHandler;
use ParsingBundle\Entity\ParsingProductInfo;
use ProductBundle\Entity\Product;
use ProductBundle\Repository\ProductRepository;
use ProductBundle\RequestObjects\GetOneProductRequest;

class ProductHandler
{
    /**
     * @var ProductRepository
     */
    private $productRepo;

    /**
     * @var RequestObjectHandler
     */
    private $requestObjectHandler;

    public function __construct(ProductRepository $productRepo, RequestObjectHandler $requestObjectHandler)
    {
        $this->productRepo = $productRepo;
        $this->requestObjectHandler = $requestObjectHandler;
    }

    public function updateProductInfo(Product $product, ProductInfoRequest $request)
    {
        $this->requestObjectHandler->validate($request, true);

        $productInfo = $product->getProductInfo();

        if (!$productInfo) {
            $productInfo = new ParsingProductInfo($request);
        }

        $productInfo->updateBaseInfo($request);
    }

    public function getOne(GetOneProductRequest $request, $groups = null)
    {
        $this->requestObjectHandler->validate($request, true, $groups);

        return $this->productRepo->getOne($request);
    }
}