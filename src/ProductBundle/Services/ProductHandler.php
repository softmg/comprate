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

    public function updateProductInfo(Product $product, ProductInfoRequest $request, $validate = true)
    {
        if ($validate) {
            $this->requestObjectHandler->validate($request, true);
        }

        $productInfo = $product->getProductInfo();

        if (!$productInfo) {
            $productInfo = new ParsingProductInfo($request);
            $product->setProductInfo($productInfo);
        }

        $productInfo->updateBaseInfo($request);
    }
}