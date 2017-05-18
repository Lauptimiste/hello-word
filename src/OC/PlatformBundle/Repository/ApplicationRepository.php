<?php

namespace OC\PlatformBundle\Repository;

/**
 * ApplicationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ApplicationRepository extends \Doctrine\ORM\EntityRepository
{

  public function getApplicationsWithAdvert($limit)
  {
    $qb = $this->createQueryBuilder('app');

    $qb
      ->innerJoin('app.advert', 'adv')  
      ->addSelect('adv')
      ->orderBy('app.id', 'DESC')
      ->setMaxResults( $limit );
      ;                  

    return $qb
      ->getQuery()
      ->getResult()
    ;
  }  

/*
  public function isFlood($ip , $time)
  {
    $this->container->get('oc_platform.validator.checkip');
  }
*/
  public function isFlood($ip, $time)
  {
    $qb = $this->createQueryBuilder('a');
    $qb
      ->where('a.ipAddress = :ip')
      ->setParameter('ip', $ip);

    $dateDiff = date('Y-m-d H:i:s', strtotime('- '.$time.'seconds'));
  
    print($dateDiff);
    $qb->andWhere('a.date > :d')
      ->setParameter('d', $dateDiff);

    return $qb
      ->getQuery()
      ->getResult()
    ;

  }   
     

}
