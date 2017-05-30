<?php

namespace ProductBundle\Repository;


use Doctrine\Common\Persistence\ObjectRepository;
use ParsingBundle\Exception\NotFoundException;

interface IRepo extends ObjectRepository
{
    /**
     * @param string $id
     * @throws NotFoundException
     *
     * @return object
     */
    public function findOrFail($id);

    /**
     * @param $object
     *
     * @return void
     */
    public function add($object);

    /**
     * @param $object
     *
     * @return void
     */
    public function remove($object);
}