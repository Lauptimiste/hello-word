<?php

namespace OC\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends Controller
{

  public function userAction($id)
  {
    $userManager = $this->get('fos_user.user_manager');
    
    // Pour charger un utilisateur
    $user = $userManager->findUserBy(array('id'=>$id));
    
    $user->setRoles(array('ROLE_ADMIN'));
    $userManager->updateUser($user); 
//    $userManager->persist($user);
    
//    $userManager->flush();
    return new Response("ok");
  }
}