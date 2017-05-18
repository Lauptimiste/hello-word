<?php
// src/OC/PlatformBundle/Validator/AntifloodValidator.php

namespace OC\PlatformBundle\Validator;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AntifloodValidator extends ConstraintValidator
{
  private $requestStack;
  private $em;
  private $serviceCheckIP;

  // Les arguments déclarés dans la définition du service arrivent au constructeur
  // On doit les enregistrer dans l'objet pour pouvoir s'en resservir dans la méthode validate()
  public function __construct(RequestStack $requestStack, EntityManagerInterface $em, $serviceCheckIP)
  {
    $this->requestStack = $requestStack;
    $this->em           = $em;
    $this->serviceCheckIP = $serviceCheckIP;
  }

  public function validate($value, Constraint $constraint)
  {
    // Pour récupérer l'objet Request tel qu'on le connait, il faut utiliser getCurrentRequest du service request_stack
    $request = $this->requestStack->getCurrentRequest();

    // On récupère l'IP de celui qui poste
    $ip = $request->getClientIp();

    $isFlood = $this->serviceCheckIP->checkIP($ip,60);
    
    if (true === $isFlood) {
      // C'est cette ligne qui déclenche l'erreur pour le formulaire, avec en argument le message
      $this->context->addViolation($constraint->message);
    }
    
  }
}