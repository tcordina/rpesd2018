<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Action
 *
 * @ORM\Table(name="action")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ActionRepository")
 */
class Action
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
     * @ORM\Column(name="nom", type="string", length=255)
     */
    private $nom;

    /**
     * @var bool
     *
     * @ORM\Column(name="jouee", type="boolean")
     */
    private $jouee;

    /**
     * @var array
     *
     * @ORM\Column(name="cartes", type="json_array", nullable=true)
     */
    private $cartes;


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
     * Set nom
     *
     * @param string $nom
     *
     * @return Action
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set jouee
     *
     * @param boolean $jouee
     *
     * @return Action
     */
    public function setJouee($jouee)
    {
        $this->jouee = $jouee;

        return $this;
    }

    /**
     * Get jouee
     *
     * @return bool
     */
    public function getJouee()
    {
        return $this->jouee;
    }

    /**
     * Set cartes
     *
     * @param array $cartes
     *
     * @return Action
     */
    public function setCartes($cartes)
    {
        $this->cartes = $cartes;

        return $this;
    }

    /**
     * Get cartes
     *
     * @return array
     */
    public function getCartes()
    {
        return $this->cartes;
    }
}

