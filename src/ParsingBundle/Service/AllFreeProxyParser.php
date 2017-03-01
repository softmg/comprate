<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 15.02.17
 * Time: 23:49
 */
namespace ParsingBundle\Service;


use Doctrine\ORM\EntityManager;
use ParsingBundle\Entity\ParsingSite;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AllFreeProxyParser
{
    /** @var  ContainerInterface */
    private $container;

    /** @var  EntityManager */
    private $em;

    /** @var bool */
    private $debug;

    /**
     * AllFreeProxyParser constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $this->container->get('doctrine.orm.entity_manager');

        $this->debug = true;
    }


    /**
     * Run parsing
     */
    public function run()
    {
        $parsingSiteRepo = $this->em->getRepository('ParsingBundle:ParsingSite');
        $freeProxySites = $parsingSiteRepo->findBy(['isFreeProxy' => 1]);

        if (count($freeProxySites)) {
            /** @var ParsingSite $freeProxySite */
            foreach ($freeProxySites as $freeProxySite) {
                $parser = $this->container->get("parsing.{$freeProxySite->getCode()}");
                if ($parser) {
                    $this->dump("get new proxies from {$freeProxySite->getUrl()}");
                    $parser->setPhantomJsService($this->container->get('parsing.phantom.client'));
                    $parser->run();
                }
            }
        }
    }

    /**
     * @param String $message
     * @param String $newLine
     */
    protected function dump($message, $newLine = "\r\n")
    {
        if ($this->debug) {
            echo "{$message}{$newLine}";
        }
    }
}
