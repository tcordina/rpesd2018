<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * UserAdmin
 *
 * @ORM\Table(name="user_admin")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserAdminRepository")
 * @Vich\Uploadable
 */
class UserAdmin extends BaseUser
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Partie", mappedBy="Joueur1")
     */
    private $parties1;
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Partie", mappedBy="Joueur2")
     */
    private $parties2;

    /**
     * @var int
     *
     * @Orm\Column(name="wins", type="integer")
     */
    private $wins;

    /**
     * @var int
     *
     * @Orm\Column(name="losses", type="integer")
     */
    private $losses;

    /**
     * @var int
     * @ORM\Column(name="elo", type="integer")
     */
    private $elo;

    /**
     * @var string
     * @ORM\Column(name="rank", type="string")
     */
    private $rank;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Message", mappedBy="joueur")
     */
    private $messages;

    /**
     * @ORM\Column(name="image_name", type="string", length=255, nullable=true)
     * @var string
     */
    private $imageName;

    /**
     * @Assert\File(maxSize="1M", mimeTypes={"image/png", "image/jpeg", "image/pjpeg", "image/gif"})
     * @Vich\UploadableField(mapping="profile_images", fileNameProperty="imageName", dimensions="[150,150]")
     * @var File
     */
    private $imageFile;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     * @var \DateTime
     */
    private $updatedAt;

    public function __construct()
    {
        parent::__construct();
        $this->setElo(1200);
        $this->setRank('argent');
        $this->setWins(0);
        $this->setLosses(0);
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
     * @return int
     */
    public function getWins()
    {
        return $this->wins;
    }

    /**
     * @param int $wins
     *
     * @return UserAdmin
     */
    public function setWins($wins)
    {
        $this->wins = $wins;

        return $this;
    }

    /**
     * @return int
     */
    public function getLosses()
    {
        return $this->losses;
    }

    /**
     * @param int $losses
     *
     * @return UserAdmin
     */
    public function setLosses($losses)
    {
        $this->losses = $losses;

        return $this;
    }

    /**
     * @param File|null $image
     */
    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;

        // VERY IMPORTANT:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost
        if ($image) {
            // if 'updatedAt' is not defined in your entity, use another property
            $this->updatedAt = new \DateTime('now');
        }
    }

    /**
     * @return File
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * Set imageName
     *
     * @param string $imageName
     *
     * @return UserAdmin
     */
    public function setImageName($imageName)
    {
        $this->imageName = $imageName;

        return $this;
    }

    /**
     * Get imageName
     *
     * @return string
     */
    public function getImageName()
    {
        return $this->imageName;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return UserAdmin
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Add parties1
     *
     * @param \AppBundle\Entity\Partie $parties1
     *
     * @return UserAdmin
     */
    public function addParties1(\AppBundle\Entity\Partie $parties1)
    {
        $this->parties1[] = $parties1;

        return $this;
    }

    /**
     * Remove parties1
     *
     * @param \AppBundle\Entity\Partie $parties1
     */
    public function removeParties1(\AppBundle\Entity\Partie $parties1)
    {
        $this->parties1->removeElement($parties1);
    }

    /**
     * Get parties1
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getParties1()
    {
        return $this->parties1;
    }

    /**
     * Add parties2
     *
     * @param \AppBundle\Entity\Partie $parties2
     *
     * @return UserAdmin
     */
    public function addParties2(\AppBundle\Entity\Partie $parties2)
    {
        $this->parties2[] = $parties2;

        return $this;
    }

    /**
     * Remove parties2
     *
     * @param \AppBundle\Entity\Partie $parties2
     */
    public function removeParties2(\AppBundle\Entity\Partie $parties2)
    {
        $this->parties2->removeElement($parties2);
    }

    /**
     * Get parties2
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getParties2()
    {
        return $this->parties2;
    }

    /**
     * Set elo
     *
     * @param integer $elo
     *
     * @return UserAdmin
     */
    public function setElo($elo)
    {
        $this->elo = $elo;
        $this->eloChange($elo);

        return $this;
    }

    /**
     * Get elo
     *
     * @return integer
     */
    public function getElo()
    {
        return $this->elo;
    }

    /**
     * Set rank
     *
     * @param string $rank
     *
     * @return UserAdmin
     */
    public function setRank($rank)
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * Get rank
     *
     * @return string
     */
    public function getRank()
    {
        return $this->rank;
    }

    private function eloChange($elo)
    {
        // switch break avec les différentes valeurs de $elo puis setRank()
    }

    /**
     * Set messages
     *
     * @param integer $messages
     *
     * @return UserAdmin
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * Get messages
     *
     * @return integer
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Add message
     *
     * @param \AppBundle\Entity\Message $message
     *
     * @return UserAdmin
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
}
