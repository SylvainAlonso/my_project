<?php

namespace RecipeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use RecipeBundle\Entity\Note;


class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction(){
        return $this->render('RecipeBundle:Default:index.html.twig');
    }
    /**
     * @Route("/new_note", name="new_note")
     */
    public function NoteAction(Request $request)
    {
      $note = new Note();
      $form = $this->createFormBuilder($note)
        ->add('title', TextType::class, array('label' => 'Titre'))
        ->add('content', TextType::class, array('label' => 'Contenu'))
        ->add('save',SubmitType::class, array('label' => 'Sauver'))
        ->getForm();
      $form->handleRequest($request);
      $note = $form->getData();

      if($form->isValid()){
        $em= $this->getDoctrine()->getManager();
        $em->persist($note);
        $em->flush();
        return new Response('La note a été ajoutée avec succès!');
      }
      return $this->render('RecipeBundle:Default:note.html.twig',array('form'=>$form->createView()));
    }
}
?>
