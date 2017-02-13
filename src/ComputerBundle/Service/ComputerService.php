<?php
namespace ComputerBundle\Service;

use Doctrine\ORM\EntityManager;

class ComputerService
{
    /** @var  EntityManager */
    private $em;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Create computer from products
     */
    public function createComputerFromProducts()
    {
        //TODO: create computer
    }
}
