<?php

namespace ApiBundle\Controller;

use ApiBundle\DataTransformer\ProductDataTransformer;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\RouteRedirectView;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use ProductBundle\Repository\ProductRepository;
use ProductBundle\Repository\ProductTypeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductController extends FOSRestController implements ClassResourceInterface
{
    /**
     * Get a single product.
     *
     * @ApiDoc(
     *   output = "ProductBundle\Entity\Product",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when not found"
     *   }
     * )
     *
     * @param int         $productId    the product id
     *
     * @throws NotFoundHttpException when does not exist
     *
     * @return View
     */
    public function getAction($productId)
    {
        /** @var ProductRepository $productRepo */
        $productRepo = $this->getRepo('Product');
        $product = $productRepo->find($productId);
        
        return $this->
            getProductDataTransformer()->
            getProductWithAttributesJson($product);
    }

    /**
     * Get a single product.
     *
     * @ApiDoc(
     *   output = "ProductBundle\Entity\Product",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when not found"
     *   }
     * )
     *
     * @param string         $type    the product type
     *
     * @throws NotFoundHttpException when does not exist
     *
     * @return View
     */
    public function listAction($type)
    {
        /** @var ProductRepository $productRepo */
        $productRepo = $this->getRepo('Product');
        /** @var ProductTypeRepository $productRepo */
        $productTypeRepo = $this->getRepo('ProductType');
        $productType = $productTypeRepo->findOneByCode($type);

        $products = $productType ? $productRepo->findBy(['type' => $productType]) : null;

        return $this->
            getProductDataTransformer()->
            getProductsWithAttributesJson($products);
    }


    /**
     * @param String
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getRepo($class)
    {
        return $this->getDoctrine()->getRepository("ProductBundle:$class");
    }

    /**
     * @return ProductDataTransformer
     */
    private function getProductDataTransformer()
    {
        return $this->get('api.data_transformer.product_data_transformer');
    }
}
