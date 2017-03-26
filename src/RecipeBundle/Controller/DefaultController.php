<?php

namespace RecipeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
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
      //Form allowing tags to be searched
      $form = $this->createFormBuilder()
              ->add('search_tag', SearchType::class, array(
              'label'=> false,
              'required'=>true,
              'attr' => array(
                    'placeholder'=>'Rechercher un tag')))
              ->getForm();
      //Retrieving database elements
      $form->handleRequest($request);
      try {
        $em = $this->getDoctrine()->getManager();
        $notes = $em->getRepository('RecipeBundle:Note')->findAll();
      }
      catch (\Doctrine\DBAL\DBALException $e) {
        $this->addFlash('failure', 'Erreur de connection à la DB');
        return $this->redirect($this->generateUrl('home'));
      }
      //Searching corresponding notes
      if ($form->isValid()) {
        $search = $form->getData()['search_tag'];
        $corresponding_notes = array();
        foreach ($notes as $note) {
          $contentxml = "<content>".$note->getContent()."</content>";
          $xml = new \DOMDocument();
          $xml->loadXML($contentxml);
          $xpath = new \DOMXPath($xml);
          $tags = $xpath->query('//tag');
          $match=false;
          foreach ($tags as $tag) {
            if ($tag->textContent == $search) {
              $match= true;
              $corresponding_notes[] = $note;
              break;
            }
          }
        }
        //If there is (are) corresponding note(s)
        if ($match==true) {
          $notes = $corresponding_notes;
        }
        else {
          $this->addFlash('failure', 'Aucune note correspondante');
        }
      }
      return $this->render('RecipeBundle:Note:index.html.twig', array('notes'=> $notes, 'form' => $form->createView()));
    }

    /**
     * @Route("/new_note", name="new_note")
     */
     public function NewNote(Request $request){
       //Creation of a new note object and calling the edit function
       $note= new Note();
       return $this->EditNote($request, $note);
     }

    /**
     * @Route("/delete_note/{id}", name="delete_note")
     */
    public function DeleteNote(Request $request, $id)
    {
      $em = $this->getDoctrine()->getManager();
      //Searching for the note corresponding to id and remove it
      $note = $em->getRepository('RecipeBundle:Note')->find($id);
      try {
        $em->remove($note);
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
        $categories = $em->getRepository('RecipeBundle:Categorie')->findAll();
      }
      catch (\Doctrine\DBAL\DBALException $e) {
        $this->addFlash('failure', 'Erreur de connection a la DB');
        return $this->redirect($this->generateUrl('home'));
      }
      //Form allowing to create/edit a note (send to the twig)
      $form = $this->createFormBuilder($note)
        ->add('title', TextType::class, array('label' => 'Titre'))
        ->add('content', TextareaType::class, array('label' => 'Contenu'))
        ->add('date', DateType::class, array('label' =>'Date'))
        ->add('categorie', ChoiceType::class, array('label'=>"Catégorie",
            'choices'=>$categories,
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
        //Check all categories
        $categories = $em->getRepository('RecipeBundle:Categorie')->findAll();
      }
      catch (\Doctrine\DBAL\DBALException $e) {
        $this->addFlash('bad', 'Erreur de connection a la DB');
        return $this->redirect($this->generateUrl('home'));
      }

      return $this->render('RecipeBundle:Note:categories.html.twig', array('categories'=> $categories));
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
     public function deleteCategorie($id) {
       $em = $this->getDoctrine()->getManager();
       $category = $em->getRepository('RecipeBundle:Categorie')->find($id);
       try {
         $em->remove($category);
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
