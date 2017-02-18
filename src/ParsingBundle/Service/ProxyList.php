<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 15.02.17
 * Time: 23:49
 */
namespace ParsingBundle\Service;

use Doctrine\ORM\EntityManager;
use ParsingBundle\Entity\ProxyIp;

class ProxyList
{
    const TIME_INTERVAL = 12; //Interval before use one ip

    /** @var  EntityManager */
    private $em;

    /** @var bool */
    private $debug = false;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;

        /* if cli command then debug = true*/
        $this->debug = php_sapi_name() == 'cli';
    }

    /**
     * Get active ip which was used self::TIME_INTERVAL seconds before NOW
     * @param boolean $updateLaunchTime
     * @return ProxyIp
     */
    public function getWhiteIp($updateLaunchTime = true)
    {
        $this->dump("- - -  Try to get white IP - - -");

        /** @var $proxyIp ProxyIp */
        while (! $proxyIp = $this->tryToGetWhiteIp()) {
            /* wait 1 second to get next ip */
            $this->dump("All proxy ips used, waiting 1 second \r\n");
            sleep(1);
        }

        $this->dump("Get white ip {$proxyIp->getIp()}");

        if ($updateLaunchTime) {
            $proxyIp->setLastUsed(new \DateTime());
            $this->em->persist($proxyIp);
            $this->em->flush();
        }

        return $proxyIp;
    }

    private function tryToGetWhiteIp()
    {
        $proxyIpRepo = $this->em->getRepository('ParsingBundle:ProxyIp');

        /* check if we have even one active IP in db */
        if (!$proxyIpRepo->findOneBy(['isActive' => 1])) {
            throw new \Exception('In database we does not have active ip');
        }

        $proxyIp = $proxyIpRepo->createQueryBuilder('ip')
            ->where('ip.isActive = 1')
            ->andWhere("ip.lastUsed <= :checkDate")
            ->setParameter('checkDate', new \DateTime("-" . self::TIME_INTERVAL . "seconds"))
            ->orderBy('ip.lastUsed', 'ASC')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult()
            ;

        return $proxyIp;
    }
    
    public function addProxyIpFail($proxyIp)
    {
        
    }

    /**
     * @param String $message
     */
    private function dump($message)
    {
        if ($this->debug) {
            echo "$message\r\n";
        }
    }
}
