<?php

namespace RecipeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use RecipeBundle\Entity\Categorie;

class CategoryApiController extends Controller{

  /**
   * @Route("/API/categories")
   * @Method("GET")
   */
   public function getCategoriesAction(){
     $serializer = $this->get('serializer');

     $em = $this->getDoctrine()->getManager();
     $categories = $em->getRepository('RecipeBundle:Categorie')->findAll();

     return new JsonResponse($serializer->serialize($categories, 'json'));
   }

  /**
   * @Route("/API/categories/{id}")
   * @Method("GET")
   */
   public function getCategoryAction(Categorie $category) {
     $serializer = $this->get('serializer');

     return new JsonResponse($serializer->serialize($category, 'json'));
   }

  /**
   * @Route("/API/categories")
   * @Method("POST")
   */
   public function newCategoryAction(Request $request) {
     $category = new Categorie();
     return $this->updateCategoryAction($request, $category);
   }

  /**
   * @Route("/API/categories/{id}")
   * @Method("PUT")
   */
   public function updateCategoryAction(Request $request, Categorie $category)
   {
     $data = json_decode($request->getContent(), true);

     $category->setName($data['name']);
     $em = $this->getDoctrine()->getManager();
     $em->persist($category);
     $em->flush();

     return new Response();
   }

  /**
   * @Route("/API/categories/{id}")
   * @Method("DELETE")
   */
   public function deleteCategoryAction(Categorie $category) {
     $em = $this->getDoctrine()->getEntityManager();
     $em->remove($category);
     $em->flush();

    return new Response();
  }

  /**
   * @Route("/API/categories/{id}")
   * @Method("OPTIONS")
   */
   public function cors() {
     $response = new Response();
     $response->headers->set('Content-Type', 'application/text');
     $response->headers->set('Access-Control-Allow-Origin', '*');
     $response->headers->set('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS');
     return $response;
   }

  /**
   * @Route("/API/categories")
   * @Method("OPTIONS")
   */
   public function cors1() {
     $response = new Response();
     $response->headers->set('Content-Type', 'application/text');
     $response->headers->set('Access-Control-Allow-Origin', '*');
     $response->headers->set('Access-Control-Allow-Methods', 'GET, PUT, POST,DELETE, OPTIONS');
     return $response;
   }
}
?>
