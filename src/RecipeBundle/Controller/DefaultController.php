<?php

namespace RecipeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
    public function ShowNotes(Request $request){
      //Form builder for the tag research, sent to the twig
      $data = array();
      $form = $this->createFormBuilder($data)
        ->add('tag', TextType::class, array('required'=>true))
        ->add('submit', SubmitType::class,
          array('label' => 'Rechercher un tag'))
        ->getForm();
      $form->handleRequest($request);
      //Try a Db request with error handling
      try {
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('RecipeBundle:Note')->findAll();
      }
      catch (\Doctrine\DBAL\DBALException $e) {
        $this->addFlash('bad', 'Erreur de connection a la DB');
        return $this->redirect($this->generateUrl('home'));
      }
      //only show notes with the tag we're looking for
      if ($form->isValid()) {
        $tag = $form->getData()['tag'];
        $filtered_notes = array();
        foreach ($product as $note) {
          $xml = new \DOMDocument();
          $xml->loadXML('<content>'.$note->getContent().'</content>');
          $xpath = new \DOMXPath($xml);
          $elements = $xpath->query('//tag');
          foreach ($elements as $element) {
            if ($element->textContent == $tag) {
              $filtered_notes[] = $note;
              break;
            }
          }
        }
        //If no note found with that tag, show an error message
        if (sizeof($filtered_notes) > 0) {
          $product = $filtered_notes;
        }
        else {
          $this->addFlash('bad', 'Aucune note correspondant à ce tag');
        }
      }
      return $this->render('RecipeBundle:Note:index.html.twig', array('notes'=> $product, 'form' => $form->createView()));
    }
    /**
     * @Route("/new_note", name="new_note")
     */
     public function NewNote(Request $request){
       $note= new Note();
       return $this->EditNote($request, $note);
     }

    /**
     * @Route("/delete_note/{id}", name="delete_note")
     */
    public function DeleteNote(note $note)
    {
      $em = $this->getDoctrine()->getManager();
      $del = $em->getRepository('RecipeBundle:Note')->find($note);
      try {
        $em->remove($del);
        $em->flush();
      }
      catch (\Doctrine\DBAL\DBALException $e) {
        $this->addFlash('failure', 'La note n a pas pu être supprimée');
        return $this->redirect($this->generateUrl('home'));
      }
      $this->addFlash('success', 'La note a bien été supprimée');
      return $this->redirect($this->generateUrl('home'));
    }

    /**
     * @Route("/edit_note/{id}", name="edit_note")
     */
    public function EditNote(Request $request,note $note){
      try {
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('RecipeBundle:Categorie')->findAll();
      }
      catch (\Doctrine\DBAL\DBALException $e) {
        $this->addFlash('bad', 'Erreur de connection a la DB');
        return $this->redirect($this->generateUrl('home'));
      }

      $form = $this->createFormBuilder($note)
        ->add('title', TextType::class, array('label' => 'Titre'))
        ->add('content', TextareaType::class, array('label' => 'Contenu'))
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
        try{
          $em= $this->getDoctrine()->getManager();
          $em->persist($note);
          $em->flush();
        }
         catch(\Doctrine\DBAL\DBALException $e){
           $this->addflash('failure', 'La note n\'a pas pu être éditée');
           return $this->redirect($this->generateUrl('home'));
         }
        $this->addflash('success', 'La note a bien été enregistrée.');
        return $this->redirect($this->generateUrl('home'));
      }
      return $this->render('RecipeBundle:Note:new_note.html.twig',array('form'=>$form->createView()));
    }

    /**
     * @Route("/check_categories", name="check_categories")
     */
    public function CheckCategories(Request $request){
      try {
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('RecipeBundle:Categorie')->findAll();
      }
      catch (\Doctrine\DBAL\DBALException $e) {
        $this->addFlash('bad', 'Erreur de connection a la DB');
        return $this->redirect($this->generateUrl('home'));
      }

      return $this->render('RecipeBundle:Note:categories.html.twig', array('categories'=> $product));
    }

    /**
     * @Route("/new_category", name="new_category")
     */
    public function NewCategory(Request $request)
    {
      $category = new Categorie();
      return $this->EditCategorie($request, $category);
    }

    /**
     * @Route("/delete_category/{id}", name="delete_category")
     */
     public function deleteCategorie(categorie $category) {
       $em = $this->getDoctrine()->getManager();
       $del = $em->getRepository('RecipeBundle:Categorie')->find($category);
       try {
         $em->remove($del);
         $em->flush();
       }
       catch (\Doctrine\DBAL\DBALException $e) {
         $this->addFlash('failure', 'La catégorie n\'a pas pu être supprimée');
         return $this->redirect($this->generateUrl('check_categories'));
       }
       $this->addFlash('success', 'La catégorie a bien été supprimée');
       return $this->redirect($this->generateUrl('check_categories'));
     }

     /**
      * @Route("/edit_category/{id}", name="edit_category")
      */
     public function EditCategorie(Request $request, categorie $category){
       $form = $this->createFormBuilder($category)
         ->add('name', TextType::class, array('label' => 'Nom'))
         ->add('save',SubmitType::class, array('label' => 'Sauver'))
         ->getForm();
       $form->handleRequest($request);
       $category = $form->getData();

       if($form->isValid()){
         try{
           $em= $this->getDoctrine()->getManager();
           $em->persist($category);
           $em->flush();
         }
         catch(\Doctrine\DBAL\DBALException $e){
           $this->addflash('failure', 'La catégorie n\'a pas pu être éditée');
           return $this->redirect($this->generateUrl('home'));
         }
         $this->addflash('success', 'La catégorie a bien été enregistrée.');
         return $this->redirect($this->generateUrl('home'));
       }
       return $this->render('RecipeBundle:Note:new_category.html.twig',array('form'=>$form->createView()));
     }

}
?>
