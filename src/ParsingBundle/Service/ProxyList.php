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

    /**
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Get active ip which was used self::TIME_INTERVAL seconds before NOW
     * @param boolean $updateLaunchTime
     * @param boolean $withLog
     * @return array
     */
    public function getWhiteIp($updateLaunchTime = true, $withLog = false)
    {
        if ($withLog) {
            echo "\r\n - - -  Try to get white IP - - - \r\n";
        }

        /** @var $proxyIp ProxyIp */
        while (! $proxyIp = $this->tryToGetWhiteIp()) {
            /* whait 1 second to get next ip */
            if ($withLog) {
                echo "All proxy ips used, waiting 1 second \r\n";
            }
            sleep(1);
        }

        if ($withLog) {
            echo "Get white ip {$proxyIp->getIp()} \r\n";
        }

        if ($updateLaunchTime) {
            $proxyIp->setLastUsed(new \DateTime());
            $this->em->persist($proxyIp);
            $this->em->flush();
        }

        return [
                $proxyIp->getIp(),
                $proxyIp->getUserAgent(),
            ];
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
}
