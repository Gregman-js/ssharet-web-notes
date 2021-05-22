<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Room
 *
 * @ORM\Table(name="room")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RoomRepository")
 */
class Room
{
    public function __construct()
    {
        $this->notes = new ArrayCollection();
        $this->members = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->created = new \DateTime('now');
        $this->edited = new \DateTime('now');
    }

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
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     * @Assert\Length(
     *      max = 25,
     *      maxMessage = "Name cannot be longer than {{ limit }} characters"
     * )
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="shortDescription", type="string", length=255, nullable=true)
     * @Assert\Length(
     *      max = 40,
     *      maxMessage = "Name cannot be longer than {{ limit }} characters"
     * )
     */
    private $shortDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=true)
     * @Assert\Length(
     *      max = 40,
     *      maxMessage = "Password cannot be longer than {{ limit }} characters"
     * )
     */
    private $password;

    /**
     * @var bool
     *
     * @ORM\Column(name="includeImages", type="boolean")
     */
    private $includeImages = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="includeFiles", type="boolean")
     */
    private $includeFiles = false;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255)
     * @Assert\NotBlank(
     *      message = "This value should not be blank"
     * )
     * @Assert\Type(
     *      type="alnum",
     *      message="The URL should contain only letters and numbers."
     *      
     * )
     * @Assert\Length(
     *      max = 25,
     *      min = 3,
     *      maxMessage = "Url cannot be longer than {{ limit }} characters",
     *      minMessage = "Url cannot be shorter than {{ limit }} characters"
     * )
     */
    private $url;

    /**
     * @var bool
     *
     * @ORM\Column(name="guestAccess", type="boolean")
     */
    private $guestAccess = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="guestEdit", type="boolean")
     */
    private $guestEdit = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="disabled", type="boolean")
     */
    private $disabled = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="public", type="boolean")
     */
    private $public = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="edited", type="datetime")
     */
    private $edited;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="rooms")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="RoomNotes", mappedBy="room")
     * @ORM\OrderBy({"edited" = "DESC"})
     */
    private $notes;

    /**
     * @ORM\OneToMany(targetEntity="RoomMember", mappedBy="room")
     */
    private $members;


    /**
     * @ORM\OneToMany(targetEntity="NoteFiles", mappedBy="room")
     * @ORM\OrderBy({"date" = "DESC"})
     */
    private $files;


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
     * @return mixed
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param mixed $files
     */
    public function setFiles($files)
    {
        $this->files = $files;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Room
     */
    public function setName($name)
    {
        $this->name = $name;
        $this->edited = new \DateTime('now');

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set shortDescription
     *
     * @param string $shortDescription
     *
     * @return Room
     */
    public function setShortDescription($shortDescription)
    {
        $this->shortDescription = $shortDescription;
        $this->edited = new \DateTime('now');

        return $this;
    }

    /**
     * Get shortDescription
     *
     * @return string
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return Room
     */
    public function setPassword($password)
    {
        $this->password = $password;
        $this->edited = new \DateTime('now');

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set includeImages
     *
     * @param boolean $includeImages
     *
     * @return Room
     */
    public function setIncludeImages($includeImages)
    {
        $this->includeImages = $includeImages;
        $this->edited = new \DateTime('now');

        return $this;
    }

    /**
     * Get includeImages
     *
     * @return bool
     */
    public function getIncludeImages()
    {
        return $this->includeImages;
    }

    /**
     * Set includeFiles
     *
     * @param boolean $includeFiles
     *
     * @return Room
     */
    public function setIncludeFiles($includeFiles)
    {
        $this->includeFiles = $includeFiles;
        $this->edited = new \DateTime('now');

        return $this;
    }

    /**
     * Get includeFiles
     *
     * @return bool
     */
    public function getIncludeFiles()
    {
        return $this->includeFiles;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return Room
     */
    public function setUrl($url)
    {
        $this->url = $url;
        $this->edited = new \DateTime('now');

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set guestAccess
     *
     * @param boolean $guestAccess
     *
     * @return Room
     */
    public function setGuestAccess($guestAccess)
    {
        $this->guestAccess = $guestAccess;
        $this->edited = new \DateTime('now');

        return $this;
    }

    /**
     * Get guestAccess
     *
     * @return bool
     */
    public function getGuestAccess()
    {
        return $this->guestAccess;
    }

    /**
     * Set guestEdit
     *
     * @param boolean $guestEdit
     *
     * @return Room
     */
    public function setGuestEdit($guestEdit)
    {
        $this->guestEdit = $guestEdit;
        $this->edited = new \DateTime('now');

        return $this;
    }

    /**
     * Get guestEdit
     *
     * @return bool
     */
    public function getGuestEdit()
    {
        return $this->guestEdit;
    }

    /**
     * Set disabled
     *
     * @param boolean $disabled
     *
     * @return Room
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;
        $this->edited = new \DateTime('now');

        return $this;
    }

    /**
     * Get disabled
     *
     * @return bool
     */
    public function getDisabled()
    {
        return $this->disabled;
    }

    /**
     * Set public
     *
     * @param boolean $public
     *
     * @return Room
     */
    public function setPublic($public)
    {
        $this->public = $public;
        $this->edited = new \DateTime('now');

        return $this;
    }

    /**
     * Get public
     *
     * @return bool
     */
    public function getPublic()
    {
        return $this->public;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Room
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set edited
     *
     * @param \DateTime $edited
     *
     * @return Room
     */
    public function setEdited($edited)
    {
        $this->edited = $edited;

        return $this;
    }

    /**
     * Get edited
     *
     * @return \DateTime
     */
    public function getEdited()
    {
        return $this->edited;
    }

    /**
     * @return int
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param int $user
     */
    public function setUser($user)
    {
        $this->user = $user;
        $this->edited = new \DateTime('now');
    }

    /**
     * @return mixed
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param mixed $notes
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
        $this->edited = new \DateTime('now');
    }

    /**
     * @return mixed
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * @param mixed $members
     */
    public function setMembers($members)
    {
        $this->members = $members;
        $this->edited = new \DateTime('now');
    }
}

