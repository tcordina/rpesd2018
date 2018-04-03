<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Partie
 *
 * @ORM\Table(name="partie")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PartieRepository")
 */
class Partie
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\UserAdmin", inversedBy="parties1", fetch="LAZY")
     */
    private $joueur1;
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\UserAdmin", inversedBy="parties2", fetch="LAZY")
     */
    private $joueur2;

    /**
     * @var array
     *
     * @ORM\Column(name="TerrainJ1", type="json_array", nullable=true)
     */
    private $terrainJ1;

    /**
     * @var array
     *
     * @ORM\Column(name="TerrainJ2", type="json_array", nullable=true)
     */
    private $terrainJ2;

    /**
     * @var array
     *
     * @ORM\Column(name="MainJ1", type="json_array", nullable=true)
     */
    private $mainJ1;

    /**
     * @var array
     *
     * @ORM\Column(name="MainJ2", type="json_array", nullable=true)
     */
    private $mainJ2;

    /**
     * @var array
     *
     * @ORM\Column(name="Pioche", type="json_array", nullable=true)
     */
    private $pioche;

    /**
     * @var array
     *
     * @ORM\Column(name="Jetons", type="json_array")
     */
    private $jetons;

    /*
     * @var int
     *
     * @ORM\Column(name="PointsJ1", type="integer")
     *
    private $points;
    */

    /**
     * @var array
     *
     * @ORM\Column(name="Actions", type="json_array")
     */
    private $actions;

    /**
     * @var int
     *
     * @ORM\Column(name="TourJoueurId", type="integer")
     */
    private $tourJoueurId;

    /**
     * @var array
     *
     * @ORM\Column(name="TourActions", type="json_array")
     */
    private $tourActions;

    /**
     * @var int
     *
     * @ORM\Column(name="manche", type="integer")
     */
    private $manche;

    /**
     * @var int
     *
     * @ORM\Column(name="carteEcartee", type="integer")
     */
    private $carteEcartee;

    /**
     * @var int
     *
     * @ORM\Column(name="winner", type="integer", nullable=true)
     */
    private $winner;

    /**
     * @var bool
     *
     * @ORM\Column(name="Ended", type="boolean")
     */
    private $ended;

    /**
     * @var Message
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Message", mappedBy="partie")
     */
    private $messages;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;


    public function __construct()
    {
        $this->createdAt = new \DateTime("now");
        $this->ended = 0;
    }



    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set plateauJ1
     *
     * @param array $plateauJ1
     *
     * @return Partie
     */
    public function setPlateauJ1($plateauJ1)
    {
        $this->plateauJ1 = $plateauJ1;

        return $this;
    }

    /**
     * Get plateauJ1
     *
     * @return array
     */
    public function getPlateauJ1()
    {
        return $this->plateauJ1;
    }

    /**
     * Set plateauJ2
     *
     * @param array $plateauJ2
     *
     * @return Partie
     */
    public function setPlateauJ2($plateauJ2)
    {
        $this->plateauJ2 = $plateauJ2;

        return $this;
    }

    /**
     * Get plateauJ2
     *
     * @return array
     */
    public function getPlateauJ2()
    {
        return $this->plateauJ2;
    }

    /**
     * Set mainJ1
     *
     * @param array $mainJ1
     *
     * @return Partie
     */
    public function setMainJ1($mainJ1)
    {
        $this->mainJ1 = $mainJ1;

        return $this;
    }

    /**
     * Get mainJ1
     *
     * @return array
     */
    public function getMainJ1()
    {
        return $this->mainJ1;
    }

    /**
     * Set mainJ2
     *
     * @param array $mainJ2
     *
     * @return Partie
     */
    public function setMainJ2($mainJ2)
    {
        $this->mainJ2 = $mainJ2;

        return $this;
    }

    /**
     * Get mainJ2
     *
     * @return array
     */
    public function getMainJ2()
    {
        return $this->mainJ2;
    }

    /**
     * Set pioche
     *
     * @param array $pioche
     *
     * @return Partie
     */
    public function setPioche($pioche)
    {
        $this->pioche = $pioche;

        return $this;
    }

    /**
     * Get pioche
     *
     * @return array
     */
    public function getPioche()
    {
        return $this->pioche;
    }

    /**
     * Set jetons
     *
     * @param array $jetons
     *
     * @return Partie
     */
    public function setJetons($jetons)
    {
        $this->jetons = $jetons;

        return $this;
    }

    /**
     * Get jetons
     *
     * @return array
     */
    public function getJetons()
    {
        return $this->jetons;
    }

    /**
     * @return int
     */
    public function getManche()
    {
        return $this->manche;
    }

    /**
     * @param int $manche
     *
     * @return Partie
     */
    public function setManche($manche)
    {
        $this->manche = $manche;

        return $this;
    }

    /**
     * Set actions
     *
     * @param array $actions
     *
     * @return Partie
     */
    public function setActions($actions)
    {
        $this->actions = $actions;

        return $this;
    }

    /**
     * Get actions
     *
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * Set tourJoueurId
     *
     * @param integer $tourJoueurId
     *
     * @return Partie
     */
    public function setTourJoueurId($tourJoueurId)
    {
        $this->tourJoueurId = $tourJoueurId;

        return $this;
    }

    /**
     * Get tourJoueurId
     *
     * @return integer
     */
    public function getTourJoueurId()
    {
        return $this->tourJoueurId;
    }

    /**
     * Set ended
     *
     * @param boolean $ended
     *
     * @return Partie
     */
    public function setEnded($ended)
    {
        $this->ended = $ended;

        return $this;
    }

    /**
     * Get ended
     *
     * @return boolean
     */
    public function getEnded()
    {
        return $this->ended;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Partie
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set joueur1
     *
     * @param \AppBundle\Entity\UserAdmin $joueur1
     *
     * @return Partie
     */
    public function setJoueur1(\AppBundle\Entity\UserAdmin $joueur1)
    {
        $this->joueur1 = $joueur1;

        return $this;
    }

    /**
     * Get joueur1
     *
     * @return \AppBundle\Entity\UserAdmin
     */
    public function getJoueur1()
    {
        return $this->joueur1;
    }

    /**
     * Set joueur2
     *
     * @param \AppBundle\Entity\UserAdmin $joueur2
     *
     * @return Partie
     */
    public function setJoueur2(\AppBundle\Entity\UserAdmin $joueur2)
    {
        $this->joueur2 = $joueur2;

        return $this;
    }

    /**
     * Get joueur2
     *
     * @return \AppBundle\Entity\UserAdmin
     */
    public function getJoueur2()
    {
        return $this->joueur2;
    }

    /**
     * Set tourActions
     *
     * @param array $tourActions
     *
     * @return Partie
     */
    public function setTourActions($tourActions)
    {
        $this->tourActions = $tourActions;

        return $this;
    }

    /**
     * Get tourActions
     *
     * @return array
     */
    public function getTourActions()
    {
        return $this->tourActions;
    }

    /**
     * Set carteEcartee
     *
     * @param integer $carteEcartee
     *
     * @return Partie
     */
    public function setCarteEcartee($carteEcartee)
    {
        $this->carteEcartee = $carteEcartee;

        return $this;
    }

    /**
     * Get carteEcartee
     *
     * @return integer
     */
    public function getCarteEcartee()
    {
        return $this->carteEcartee;
    }

    /**
     * @return int
     */
    public function getWinner()
    {
        return $this->winner;
    }

    /**
     * @param int $winner
     *
     * @return Partie
     */
    public function setWinner($winner)
    {
        $this->winner = $winner;

        return $this;
    }

    /**
     * Set terrainJ1
     *
     * @param array $terrainJ1
     *
     * @return Partie
     */
    public function setTerrainJ1($terrainJ1)
    {
        $this->terrainJ1 = $terrainJ1;

        return $this;
    }

    /**
     * Get terrainJ1
     *
     * @return array
     */
    public function getTerrainJ1()
    {
        return $this->terrainJ1;
    }

    /**
     * Set terrainJ2
     *
     * @param array $terrainJ2
     *
     * @return Partie
     */
    public function setTerrainJ2($terrainJ2)
    {
        $this->terrainJ2 = $terrainJ2;

        return $this;
    }

    /**
     * Get terrainJ2
     *
     * @return array
     */
    public function getTerrainJ2()
    {
        return $this->terrainJ2;
    }

    /**
     * Add message
     *
     * @param \AppBundle\Entity\Message $message
     *
     * @return Partie
     */
    public function addMessage(\AppBundle\Entity\Message $message)
    {
        $this->messages[] = $message;

        return $this;
    }

    /**
     * Remove message
     *
     * @param \AppBundle\Entity\Message $message
     */
    public function removeMessage(\AppBundle\Entity\Message $message)
    {
        $this->messages->removeElement($message);
    }

    /**
     * Get messages
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
