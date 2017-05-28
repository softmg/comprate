<?php
namespace ComputerBundle\Service;

use ComputerBundle\Entity\Computer;

class ComputerBuilder
{
    /**
     * Create computer from products
     *
     * @param \ProductBundle\Entity\Product[] $products
     */
    public function createComputerFromProducts($products)
    {
        $computer = new Computer();
        
        foreach ($products as $product) {
            $computer->addProduct($product);
        }
        
        
        //TODO: validate computer
    }
}
