<?php
namespace ApiBundle\DataTransformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use ProductBundle\Entity\Attribute;
use ProductBundle\Entity\Product;
use ProductBundle\Repository\ProductAttributeRepository;

class ProductDataTransformer
{

    /** @var  EntityManager */
    private $em;

    /**
     * ProductDataTransformer constructor.
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Get product with attributes
     * @param Product $product
     * @return Array
     */
    public function getProductWithAttributesArray(Product $product)
    {
        $result = [];
        if ($product) {
            $result = [
                'name' => $product->getName(),
                'rate' => $product->getRate(),
                'price' => $product->getPrice()
            ];

            /* get product type attributes */
            $productTypeAttributes = $product->getType()->getAttributes();
            /** @var ProductAttributeRepository $productAttributesRepo */
            $productAttributesRepo = $this->em->getRepository('ProductBundle:ProductAttribute');

            if (count($productTypeAttributes)) {
                /** @var Attribute $attribute */
                foreach ($productTypeAttributes as $attribute) {
                    $productAttribute = $productAttributesRepo->findOneBy([
                        'attribute' => $attribute,
                        'product'   => $product
                    ]);
                    $result[$attribute->getCode()] = $productAttribute ? $productAttribute->getValue() : '';
                }
            }
        }

        return $result;
    }

    /**
     * Get JSON responce product with attributes
     * @param Product $product
     * @return String
     */
    public function getProductWithAttributesJson(Product $product)
    {
        $resultAr = $this->getProductWithAttributesArray($product);

        return \GuzzleHttp\json_encode($resultAr);
    }

    /**
     * Get JSON responce products with attributes
     * @param array $products
     * @return String
     */
    public function getProductsWithAttributesJson(array $products)
    {
        $result = [];
        if (count($products)) {
            foreach ($products as $product) {
                $result[] =  $this->getProductWithAttributesArray($product);
            }
        }

        return \GuzzleHttp\json_encode($result);
    }
}
