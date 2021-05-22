<?php
// src/AppBundle/Entity/User.php

namespace AppBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{

    public function __construct()
    {
        parent::__construct();
        // your own logic
        $this->roles = array('ROLE_USER');
        $this->rooms = new ArrayCollection();
        $this->roomNotes = new ArrayCollection();
        $this->roomMembers = new ArrayCollection();
    }

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="Room", mappedBy="user")
     */
    private $rooms;

    /**
     * @ORM\OneToMany(targetEntity="RoomNotes", mappedBy="user")
     */
    private $roomNotes;

    /**
     * @ORM\OneToMany(targetEntity="NoteFiles", mappedBy="user")
     */
    private $roomFiles;

    /**
     * @ORM\OneToMany(targetEntity="RoomMember", mappedBy="user")
     */
    private $roomMembers;
    
    /**
     * @ORM\ManyToOne(targetEntity="Plan", inversedBy="users")
     * @ORM\JoinColumn(name="plan_id", referencedColumnName="id")
     */
    private $plan;

    /**
     * @var string
     *
     * @ORM\Column(name="firstName", type="string", length=255, nullable=true)
     * @Assert\Length(
     *      max = 50,
     *      maxMessage = "Your first name cannot be longer than {{ limit }} characters"
     * )
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="lastName", type="string", length=255, nullable=true)
     * @Assert\Length(
     *      max = 50,
     *      maxMessage = "Your last name cannot be longer than {{ limit }} characters"
     * )
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="aboutMe", type="string", length=255, nullable=true)
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "About me section cannot be longer than {{ limit }} characters"
     * )
     */
    private $aboutMe;

    /**
     * @var string
     *
     * @ORM\Column(name="activeColor", type="string", length=255)
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Filed cannot be longer than {{ limit }} characters"
     * )
     */
    private $activeColor = 'primary';

    /**
     * @var string
     *
     * @ORM\Column(name="startPage", type="string", length=255)
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Filed cannot be longer than {{ limit }} characters"
     * )
     */
    private $startPage = 'panel';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pictureFilename;

    /**
     * Set pictureFilename
     *
     * @param string $pictureFilename
     *
     * @return User
     */
    public function setPictureFilename($pictureFilename)
    {
        $this->pictureFilename = $pictureFilename;

        return $this;
    }

    /**
     * Get pictureFilename
     *
     * @return string
     */
    public function getPictureFilename()
    {
        return $this->pictureFilename;
    }

    /**
     * @return mixed
     */
    public function getRooms()
    {
        return $this->rooms;
    }

    /**
     * @param mixed $rooms
     */
    public function setRooms($rooms)
    {
        $this->rooms = $rooms;
    }

    /**
     * @return mixed
     */
    public function getRoomNotes()
    {
        return $this->roomNotes;
    }

    /**
     * @param mixed $roomNotes
     */
    public function setRoomNotes($roomNotes)
    {
        $this->roomNotes = $roomNotes;
    }

    /**
     * @return mixed
     */
    public function getRoomFiles()
    {
        return $this->roomFiles;
    }

    /**
     * @param mixed $roomFiles
     */
    public function setRoomFiles($roomFiles)
    {
        $this->roomFiles = $roomFiles;
    }

    /**
     * @return mixed
     */
    public function getRoomMembers()
    {
        return $this->roomMembers;
    }

    /**
     * @param mixed $roomMembers
     */
    public function setRoomMembers($roomMembers)
    {
        $this->roomMembers = $roomMembers;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set aboutMe
     *
     * @param string $aboutMe
     *
     * @return User
     */
    public function setAboutMe($aboutMe)
    {
        $this->aboutMe = $aboutMe;

        return $this;
    }

    /**
     * Get aboutMe
     *
     * @return string
     */
    public function getAboutMe()
    {
        return $this->aboutMe;
    }

    /**
     * Set activeColor
     *
     * @param string $activeColor
     *
     * @return User
     */
    public function setActiveColor($activeColor)
    {
        $this->activeColor = $activeColor;

        return $this;
    }

    /**
     * Get activeColor
     *
     * @return string
     */
    public function getActiveColor()
    {
        return $this->activeColor;
    }

    /**
     * Set startPage
     *
     * @param string $startPage
     *
     * @return User
     */
    public function setStartPage($startPage)
    {
        $this->startPage = $startPage;

        return $this;
    }

    /**
     * Get startPage
     *
     * @return string
     */
    public function getStartPage()
    {
        return $this->startPage;
    }

    /**
     * @return int
     */
    public function getPlan()
    {
        return $this->plan;
    }

    /**
     * @param int $plan
     */
    public function setPlan($plan)
    {
        $this->plan = $plan;
    }

}