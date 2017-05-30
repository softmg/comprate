<?php


namespace ProductBundle\Entity;


interface IToArray
{
    /**
     * @return mixed[]
     */
    public function toArray(): array;
}