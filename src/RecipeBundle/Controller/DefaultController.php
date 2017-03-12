<?php

namespace RecipeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use RecipeBundle\Entity\Note;
use RecipeBundle\Entity\Categorie;


class DefaultController extends Controller
{
    /**
     * @Route("/", name="home")
     */
    public function indexAction(){
      $em = $this->getDoctrine()->getManager();
      $product = $em->getRepository('RecipeBundle:Note')->findAll();
      return $this->render('RecipeBundle:Note:index.html.twig', array('notes'=> $product));
    }
    /**
     * @Route("/new_note", name="new_note")
     */
    public function NewNote(Request $request)
    {
      $note = new Note();
      $em = $this->getDoctrine()->getManager();
      $product = $em->getRepository('RecipeBundle:Categorie')->findAll();
      $form = $this->createFormBuilder($note)
        ->add('title', TextType::class, array('label' => 'Titre'))
        ->add('content', TextType::class, array('label' => 'Contenu'))
        ->add('date', DateType::class, array('label' =>'Date'))
        ->add('categorie', ChoiceType::class, array('label'=>"Catégorie",
            'choices'=>$product,
            'choice_label' => function($cat, $key, $index){
              return $cat->getName();
        }))

        ->add('save',SubmitType::class, array('label' => 'Sauver'))
        ->getForm();
      $form->handleRequest($request);
      $note = $form->getData();

      if($form->isValid()){
        $em= $this->getDoctrine()->getManager();
        $em->persist($note);
        $em->flush();
        $this->addflash('notice', 'La note a bien été enregistrée.');
        return $this->redirect($this->generateUrl('home'));
      }
      return $this->render('RecipeBundle:Note:new_note.html.twig',array('form'=>$form->createView()));
    }

    /**
     * @Route("/check_categories", name="check_categories")
     */
    public function CheckCategories(Request $request)
    {
      $em = $this->getDoctrine()->getManager();
      $product = $em->getRepository('RecipeBundle:Categorie')->findAll();
      return $this->render('RecipeBundle:Note:categories.html.twig', array('categories'=> $product));
    }

    /**
     * @Route("/new_category", name="new_category")
     */
    public function NewCategory(Request $request)
    {
      $category = new Categorie();
      $form = $this->createFormBuilder($category)
        ->add('name', TextType::class, array('label' => 'Nom'))
        ->add('save',SubmitType::class, array('label' => 'Sauver'))
        ->getForm();
      $form->handleRequest($request);
      $category = $form->getData();

      if($form->isValid()){
        $em= $this->getDoctrine()->getManager();
        $em->persist($category);
        $em->flush();
        $this->addflash('notice', 'La catégorie a bien été enregistrée.');
        return $this->redirect($this->generateUrl('home'));
      }
      return $this->render('RecipeBundle:Note:new_category.html.twig',array('form'=>$form->createView()));
    }


}
?>
