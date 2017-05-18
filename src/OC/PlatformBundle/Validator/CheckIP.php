<?php

namespace OC\PlatformBundle\Validator;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Application;

class CheckIP 
{
    private $request;
    private $em;
    private $AdRepository;
    private $AppRepository;    
    
    public function __construct($request, \Doctrine\ORM\EntityManager $entityManager)
    {
      $this->em = $entityManager;
      $this->AdRepository = $this->em->getRepository('OCPlatformBundle:Advert');
      $this->AppRepository = $this->em->getRepository('OCPlatformBundle:Application');      
      $this->request = $request;
    }
    
    public function checkIP($ip,$time)
    {
          $ListApps = $this->AppRepository->isFlood($ip,$time);
          $ListAds = $this->AdRepository->isFlood($ip,$time);
          
          if(count($ListApps) > 0 || count($ListAds) > 0)
          {
            return true;
          }    
          else
          {
            return false;
          }
    }
    
    

}