<?php

namespace ParsingBundle\Exception;


use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotFoundException extends NotFoundHttpException
{
    public function __construct($entityId, \Exception $previous = null, $code = 0)
    {
        $message = sprintf('The entity with id "%s" not found.', $entityId);

        parent::__construct($message, $previous, $code);
    }
}