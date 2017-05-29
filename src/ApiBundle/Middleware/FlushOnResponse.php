<?php

namespace ApiBundle\Middleware;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class FlushOnResponse
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest() || !$this->em->isOpen()) {
            return;
        };

        $this->em->flush();
    }
}