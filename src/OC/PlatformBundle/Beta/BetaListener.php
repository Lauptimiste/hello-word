<?php
// src/OC/PlatformBundle/Beta/BetaListener.php

namespace OC\PlatformBundle\Beta;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class BetaListener
{
  // Notre processeur
  protected $betaHTML;

  // La date de fin de la version bêta :
  // - Avant cette date, on affichera un compte à rebours (J-3 par exemple)
  // - Après cette date, on n'affichera plus le « bêta »
  protected $endDate;

  public function __construct(BetaHTMLAdder $betaHTML, $endDate)
  {
    $this->betaHTML = $betaHTML;
    $this->endDate  = new \Datetime($endDate);
  }
  // L'argument de la méthode est un FilterResponseEvent
  public function processBeta(FilterResponseEvent $event)
  {
    // On teste si la requête est bien la requête principale (et non une sous-requête)
    if (!$event->isMasterRequest()) {
      return;
    }

    if($this->endDate < new \Datetime())  return;
        
    $remainingDays = $this->endDate->diff(new \Datetime())->days;
    // On utilise notre BetaHTML
    $response = $this->betaHTML->addBeta($event->getResponse(), $remainingDays);
    
    // Puis on insère la réponse modifiée dans l'évènement
    $event->setResponse($response);
  }
}