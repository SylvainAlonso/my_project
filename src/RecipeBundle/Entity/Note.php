<?php
namespace RecipeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
//use RecipeBundle\Entity\Categorie;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Note
 *
 * @ORM\Table(name="note")
 * @ORM\Entity(repositoryClass="RecipeBundle\Repository\NoteRepository")
 */
class Note
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;
    /**
     * @var string
     *
     * @ORM\Column(name="content", type="string", length=3000)
     */
    private $content;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * Many Notes have One Categorie.
     * @ORM\ManyToOne(targetEntity="RecipeBundle\Entity\Categorie", inversedBy="note")
     * @ORM\JoinColumn(name="Categorie_id", referencedColumnName="id")
     */
    private $categorie;

    //auto completing date time with actual date time value
     public function __construct() {
         $this->date = new \DateTime();
     }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * Set title
     *
     * @param string $title
     *
     * @return Note
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
    /**
     * Set content
     *
     * @param string $content
     *
     * @return Note
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }
    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return note
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }
    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set categorie
     *
     * @param \RecipeBundle\Entity\Categorie $categorie
     *
     * @return Note
     */
    public function setCategorie(\RecipeBundle\Entity\Categorie $categorie = null)
    {
        $this->categorie = $categorie;
        return $this;
    }

    /**
     * Get categorie
     *
     * @return \RecipeBundle\Entity\Categorie
     */
    public function getCategorie()
    {
        return $this->categorie;
    }

  /**
   * @Assert\IsTrue(message = "Xml non valide")
   */
  public function isValid(){
    //. pour concatener
    $contentxml = "<content>".$this->content."</content>";
    //var_dump($contentxml);
    //die();
    try {
      $dom = new \DOMDocument();
      $dom->loadXML($contentxml);
      $dom->schemaValidate("xmlschema.xsd");
    }
    catch( \ErrorException $e) {
      return false;
    }
    return true;
  }
}
