<?php

namespace OC\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class GeneralController extends Controller
{
    public function indexAction(Request $request)
    {
        $session = $request->getSession();
            
        $session->getFlashBag()->add('info', 'La page de contact n\'est pas encore disponible, merci de revenir plus tard.');    
    
        return $this->render('OCCoreBundle:General:index.html.twig');
    }     
    public function contactAction(Request $request)
    {
        $session = $request->getSession();
            
        $session->getFlashBag()->add('info', 'La page de contact n\'est pas encore disponible, merci de revenir plus tard.');    
       
        return $this->render('OCCoreBundle:General:contact.html.twig');
    }
    
    
}
