<?php

namespace OC\PlatformBundle\Purger;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Application;

class AdvertPurge
{
    private $mailer;
    private $locale;
    private $em;
    
    public function __construct(\OC\PlatformBundle\Email\ApplicationMailer $mailer, $locale, \Doctrine\ORM\EntityManager $entityManager)
    {
      $this->em = $entityManager;
      $this->repository = $this->em->getRepository('OCPlatformBundle:Advert');
      $this->locale = $locale;
      $this->mailer = $mailer;
    }

    public function purge($days)
    {
      $adverts = $this->repository->findToPurge($days); 
      if(count($adverts) == 0) 
      {
        echo ("aucune annonce ne sera supprimée<br />");
        return;
      }    
    
      $message = "les annonces<br />";
      foreach($adverts as $advert)
      {
        $message .= $advert->getTitle()." by ".$advert->getAuthor()."<br />";     
      }
      $message .= "sont supprimées<br />";
      
      echo($message);
      
      $this->repository->purgeFromDays($adverts);      
    }
    
}