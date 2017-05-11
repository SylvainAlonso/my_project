<?php
namespace RecipeBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use RecipeBundle\Entity\Note;
use RecipeBundle\Entity\Categorie;


class NoteApiController extends Controller{

  /**
   * @Route("/API/notes")
   * @Method("GET")
   */
   public function getNotesAction() {
     //Recovery of the doctrine EntityManager
     $em = $this->getDoctrine()->getManager();
     $notes = $em->getRepository('RecipeBundle:Note')->findAll();
     //Return JSON object for each note
     $serializer = $this->get('serializer');
     return new JsonResponse($serializer->serialize($notes, 'json'));
    }

    /**
     * @Route("/API/notes/{id}")
     *  @Method("GET")
     */
     public function getNoteAction(Note $note) {
      $serializer = $this->get('serializer');

      return new JsonResponse($serializer->serialize($note, 'json'));
    }

    /**
     * @Route("/API/notes")
     * @Method("POST")
     */
     public function newNoteAction(Request $request) {
       $note = new Note();
       return $this->updateNoteAction($request, $note);
     }

    /**
     * @Route("/API/notes/{id}")
     * @Method("PUT")
     */
     public function updateNoteAction(Request $request, Note $note) {
       //Decode the received JSON object
       $data = json_decode($request->getContent(), true);

       $em = $this->getDoctrine()->getManager();
       $category = $em->getRepository('RecipeBundle:Categorie')
                                      ->find($data['categorie']);

       //Put datas into that decoded object
       $note->setTitle($data['title']);
       $note->setContent($data['content']);
       $note->setCategorie($category);
       $em->persist($note);
       $em->flush();

       return new Response();
     }

     /**
      * @Route("/API/notes/{id}")
      * @Method("DELETE")
      */
      public function deleteNoteAction(Note $note) {
        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($note);
        $em->flush();
        return new Response();
      }

     /**
      * @Route("/API/notes/{id}")
      * @Method("OPTIONS")
      */
      public function cors() {
        /*Management of the CORS in a different way than installing an extension
        to the browser*/
        $response = new Response();
        $response->headers->set('Content-Type', 'application/text');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, OPTIONS');
        return $response;
      }
   }
?>
