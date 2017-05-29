<?php

namespace ApiBundle\Middleware;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;

class FlushOnConsoleTerminate
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function onConsoleTerminate(ConsoleTerminateEvent $event)
    {
        if (!$this->em->isOpen()) {
            return;
        }

        $this->em->flush();
    }
}