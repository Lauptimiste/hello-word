<?php

namespace OC\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Image;
use OC\PlatformBundle\Entity\Application;
use OC\PlatformBundle\Entity\AdvertSkill;
use OC\PlatformBundle\Purger\AdvertPurge;      
  
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use OC\PlatformBundle\Form\AdvertType;
use OC\PlatformBundle\Form\AdvertEditType;
use OC\PlatformBundle\Form\AdvertDeleteType;
use OC\PlatformBundle\Form\ApplicationType;

use OC\PlatformBundle\Event\MessagePostEvent;
use OC\PlatformBundle\Event\PlatformEvents;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class AdvertController extends Controller
{
  public function indexAction($page)
  {
    if ($page < 1) {
      throw new NotFoundHttpException('Page "'.$page.'" inexistante.');
    }

    // Mais bien sûr il faudrait utiliser un paramètre, et y accéder via $this->container->getParameter('nb_per_page')
    $nbPerPage = 3;
    
    $em = $this->getDoctrine()->getManager();
    $listAdverts = $em->getRepository('OCPlatformBundle:Advert')->getAdverts($page,$nbPerPage);
  
    $nbPages = ceil(count($listAdverts) / $nbPerPage);
    if ($page > $nbPages) {
      throw $this->createNotFoundException('Page "'.$page.'" inexistante.');
    }
    
    return $this->render('OCPlatformBundle:Advert:index.html.twig', array(
      'listAdverts' => $listAdverts,
      'nbPages' => $nbPages,
      'page' => $page,
    ));
  }

  public function viewAction(Advert $advert)
  {      
    $em = $this->getDoctrine()
      ->getManager()
    ;

    // On récupère la liste des candidatures de cette annonce
    $listApplications = $em
      ->getRepository('OCPlatformBundle:Application')
      ->findBy(array('advert' => $advert))
    ;

    // On récupère maintenant la liste des AdvertSkill
    $listAdvertSkills = $em
      ->getRepository('OCPlatformBundle:AdvertSkill')
      ->findBy(array('advert' => $advert))
    ;
                                         
    return $this->render('OCPlatformBundle:Advert:view.html.twig', array(
      'advert'           => $advert,
      'listApplications' => $listApplications,
      'listAdvertSkills' => $listAdvertSkills,
    )); 
  
  }

  /**
   * @Security("has_role('ROLE_AUTEUR')")
   */
  public function addAction(Request $request)
  { 
      
    $advert = new Advert;
    $form = $this->createForm(AdvertType::class, $advert);
    
   // Si la requête est en POST, c'est que le visiteur a soumis le formulaire
    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) 
    {
      $advert->setIpAddress($request->getClientIp());
      
      $security = $this->get('security.token_storage');
      
      // On récupère le token
      $token = $security->getToken();
      $user = $token->getUser();
            
      $advert->setAuthor($user);
    // On crée l'évènement avec ses 2 arguments
      $event = new MessagePostEvent($advert->getContent(), $user);

      // On déclenche l'évènement
      $this->get('event_dispatcher')->dispatch(PlatformEvents::POST_MESSAGE, $event);

      // On récupère ce qui a été modifié par le ou les listeners, ici le message
      $advert->setContent($event->getMessage());
            
      $em = $this->getDoctrine()->getManager();
      $em->persist($advert);        
      $em->flush();

      $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

      // On redirige vers la page de visualisation de l'annonce nouvellement créée
      return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
    }
    // On passe la méthode createView() du formulaire à la vue
    // afin qu'elle puisse afficher le formulaire toute seule
    return $this->render('OCPlatformBundle:Advert:add.html.twig', array(
      'form' => $form->createView(),
    ));  
 
  }  
  
  public function editAction($id, Request $request)
  {
    // Ici, on récupérera l'annonce correspondante à $id
    $em = $this->getDoctrine()->getManager();

    // On récupère l'annonce $id
    $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

    if (null === $advert) {
      throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
    }

    $form = $this->createForm(AdvertEditType::class, $advert);
  
   // Si la requête est en POST, c'est que le visiteur a soumis le formulaire
    if ($request->isMethod('POST')) {
      // On fait le lien Requête <-> Formulaire
      // À partir de maintenant, la variable $advert contient les valeurs entrées dans le formulaire par le visiteur
      $form->handleRequest($request);

      // On vérifie que les valeurs entrées sont correctes
      // (Nous verrons la validation des objets en détail dans le prochain chapitre)
      if ($form->isValid()) {
        $em->flush();

        $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');

        // On redirige vers la page de visualisation de l'annonce nouvellement créée
        return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
      }
    }
  
    return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
      'advert' => $advert,
      'form' => $form->createView(),      
    ));
  }  
  
  public function deleteAction($id, Request $request)
  {
    $em = $this->getDoctrine()->getManager();

    // On récupère l'annonce $id
    $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

    if (null === $advert) {
      throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
    }

    $form = $this->createForm(AdvertDeleteType::class, $advert);
   // Si la requête est en POST, c'est que le visiteur a soumis le formulaire
    if ($request->isMethod('POST')  && $form->handleRequest($request)->isValid()) {

      // On boucle sur les catégories de l'annonce pour les supprimer
      foreach ($advert->getCategories() as $category) {
        $advert->removeCategory($category);
      }
      
      $em->remove($advert);     
      $em->flush();
      
      $request->getSession()->getFlashBag()->add('notice', 'Annonce bien supprimée.');      
      return $this->redirectToRoute('oc_platform_home', array('page' => 1));      
    }
    
    return $this->render('OCPlatformBundle:Advert:delete.html.twig', array(
      'advert' => $advert,
      'form' => $form->createView(),      
    ));
  }  

  public function applyAction($id, Request $request)
  {
    $application = new Application;
    $form = $this->createForm(ApplicationType::class, $application);

   // Si la requête est en POST, c'est que le visiteur a soumis le formulaire
    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) 
    {
      $em = $this->getDoctrine()->getManager();   
    
      $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);
      $application->setAdvert($advert);
      $application->setIpAddress($request->getClientIp());
      
      $em->persist($application);     
      $em->flush();

      $request->getSession()->getFlashBag()->add('notice', 'Votre candidature a bien été enregistrée.');

      // On redirige vers la page de visualisation de l'annonce nouvellement créée
      return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
    }
    // On passe la méthode createView() du formulaire à la vue
    // afin qu'elle puisse afficher le formulaire toute seule
    return $this->render('OCPlatformBundle:Advert:application.html.twig', array(
      'form' => $form->createView(),
    ));  

  }  
  public function menuAction($limit)
  {
    $em = $this->getDoctrine()->getManager();
    $listAdverts = $em->getRepository('OCPlatformBundle:Advert')->findBy(
	  array(),
	  array('date' => 'desc'),
	  $limit
  	);
  
    return $this->render('OCPlatformBundle:Advert:menu.html.twig', array(
      'listAdverts' => $listAdverts
    ));
  }     

  public function purgeAction($days)
  {
   /* Accès au service */
    $Advertpurge = $this->get('oc_platform.purger.advert');
    
    $Advertpurge->purge($days);
        
    return new Response("<body>les annonces qui n'ont pas eu de candidatures et vieilles de plus de ".$days." jours ont été supprimées</body>");
  }  
  
  public function testAction()   //juste pour voir...
  {
    $advert = new Advert;
        
    $advert->setDate(new \Datetime());  // Champ « date » OK
    $advert->setTitle('abc');           // Champ « title » incorrect : moins de 10 caractères
    //$advert->setContent('blabla');    // Champ « content » incorrect : on ne le définit pas
    $advert->setAuthor('A');            // Champ « author » incorrect : moins de 2 caractères
        
    // On récupère le service validator
    $validator = $this->get('validator');
        
    // On déclenche la validation sur notre object
    $listErrors = $validator->validate($advert);

    // Si $listErrors n'est pas vide, on affiche les erreurs
    if(count($listErrors) > 0) {
      // $listErrors est un objet, sa méthode __toString permet de lister joliement les erreurs
      return new Response((string) $listErrors);
    } else {
      return new Response("L'annonce est valide !");
    }
  }   
  
  /**
   * @ParamConverter("json")
   */
  public function ParamConverterAction($json)
  {
    return new Response(print_r($json, true));
  }       

}