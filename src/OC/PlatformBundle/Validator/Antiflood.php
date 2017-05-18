<?php

namespace OC\PlatformBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Antiflood extends Constraint
{
  public $message = "Vous venez de poster une annonce!";
  
  public function validatedBy()
  {
    return 'oc_platform_antiflood'; // Ici, on fait appel à l'alias du service
  }  
}