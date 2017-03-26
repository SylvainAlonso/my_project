<?php

namespace RecipeBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use RecipeBundle\Entity\Note;
use RecipeBundle\Entity\Categorie;

class APIController extends Controller{
  /**
   * @Route("API/notes")
   * @Method("GET")
   */
  public function showNotesAction(){
    try{
      $em = $this->getDoctrine()->getManager();
      $notes = $em->getRepository('RecipeBundle:Note')->findAll();
    }
    catch(\Doctrine\DBAL\DBALException $e){
      $this->addFlash('failure', 'Erreur de connection à la DB');
    }

    //Serialization of data + send Json object
    $serializer = $this->get('serializer');
    return new JsonResponse($serializer->serialize($notes, 'json'));
  }

  /**
   * @Route("API/new_note")
   * @Method("POST")
   */
  public function newNoteAction(Request $request){
    $note = new note;
    return $this->editNoteAction($request, $note);
  }

  /**
   * @Route("API/delete_note/{id}")
   * @Method("DELETE")
   */
  public function deleteNoteAction(Request $request){
    $em = $this->getDoctrine()->getManager();
    $note = $em->getRepository('RecipeBundle:Note')->find($id);
    if(!$note){
      return new Repsonse("La note n'a pas été trouvée");
    }
    try{
      $em->remove($note);
      $em->flush();
      return new Response("success");
    }
    catch(\Doctrine\DBAL\DBALException $e){
      return new Response("failure");
    }

  }

  /**
   * @Route("API/edit_note/{id}")
   * @Method("PUT")
   */
   public function editNoteAction(Request $request, note $note){
     //decode the json object in a local vairable
      //Recupération des éléments du JSON envoyé
    $em = $this->getDoctrine()->getManager();
    $json = $request->getContent();
    $data = json_decode($json, true);

    $category = $em->getRepository('RecipeBundle:Categorie')->find($data['categorie']);
    if(!$cat){
      return new Response("La catégorie n'a pas pu être trouvée");
    }
    //put datas into that decoded object
    $note->setTitle($data['title']);
    $note->setContent($data['content']);
    $note->setCategorie($category);
    $em = $this->getDoctrine()->getManager();
    $em->persist($note);
    try{
      $em->flush();
      return new Response("success");
    }
    catch(\Doctrine\DBAL\DBALException $e){
      return new Response("failure");
    }
   }

  /**
   * @Route("/API/check_categories")
   * @Method("GET")
   */
   public function checkCategoriesAction(){
     try{
       $em = $this->getDoctrine()->getManager();
       $categories = $em->getRepository('RecipeBundle:Categorie')->findAll();
     }
     catch(\Doctrine\DBAL\DBALException $e){
       $this->addFlash('failure', 'Problème de récupération des catégories');
     }

     //send a serialized json object for each note
     $serializer = $this->get('serializer');
     return new JsonResponse($serializer->serialize($categories, 'json'));
   }
  /**
   * @Route("/API/new_category")
   * @Method("POST")
   */
   public function newCategoryAction(Request $request) {
       $category = new categorie;
       return $this->updateCategorieAction($request, $category);
   }

  /**
   * @Route("/API/delete_category/{id}")
   * @Method("DELETE")
   */
  public function deleteCategoryAction($id){
    $em = $this->getDoctrine()->getManager();
    $category = $em->getRepository('RecipeBundle:categorie')->find($id);
    if(!$category){
      return new Response("Catégorie non trouvée");
    }
    try{
      $em->remove($category);
      $em->flush();
      return new Response("success");
    }
    catch(\Doctrine\DBAL\DBALException $e){
      return new Response("failure");
    }
  }
 /**
  * @Route("/API/edit_category/{id}")
  * @Method("PUT")
  */
  public function editCategorieAction(Request $request, categorie $cat) {
    //decode json object on a local vairable
    //Recupération des infos
    $em = $this->getDoctrine()->getManager();
    $json = $request->getContent();
    $data = json_decode($json, true);
    //update decoded object's data
    $cat->setName($data['name']);
    $em = $this->getDoctrine()->getManager();
    try{
      $em->persist($cat);
      $em->flush();
      return new Response("success");
    }
    catch(\Doctrine\DBAL\DBALException $e){
      return new Response("failure");
    }
  }
}

?>
