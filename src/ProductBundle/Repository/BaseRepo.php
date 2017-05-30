<?php

namespace ProductBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ParsingBundle\Exception\NotFoundException;

/**
 * Базовые классы это не хорошо, но (мне лень) репозитории относятся к инфраструктуре, поэтому здесь можно.
 */
class BaseRepo extends EntityRepository implements IRepo
{
    /**
     * @param string $id
     * @throws NotFoundException
     *
     * @return object
     */
    public function findOrFail($id)
    {
        $entity = $this->find($id);

        if ($entity === null) {
            throw new NotFoundException($id);
        }

        return $entity;
    }

    public function add($object)
    {
        $this->getEntityManager()->persist($object);
    }

    public function remove($object)
    {
        $this->getEntityManager()->persist($object);
    }
}