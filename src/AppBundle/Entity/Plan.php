<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Plan
 *
 * @ORM\Table(name="plan")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PlanRepository")
 */
class Plan
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="maxRooms", type="integer")
     */
    private $maxRooms;

    /**
     * @var int
     *
     * @ORM\Column(name="maxRoomsNotes", type="integer")
     */
    private $maxRoomsNotes;

    /**
     * @var bool
     *
     * @ORM\Column(name="isSharing", type="boolean")
     */
    private $isSharing;

    /**
     * @var bool
     *
     * @ORM\Column(name="isPublic", type="boolean")
     */
    private $isPublic;

    /**
     * @var bool
     *
     * @ORM\Column(name="isMonthly", type="boolean")
     */
    private $isMonthly;

    /**
     * @var int
     *
     * @ORM\Column(name="maxFilePerRoom", type="integer")
     */
    private $maxFilePerRoom;

    /**
     * @var float
     *
     * @ORM\Column(name="maxFileSize", type="float")
     */
    private $maxFileSize;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float")
     */
    private $price;

    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="plan")
     */
    private $users;


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Plan
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set maxRooms.
     *
     * @param int $maxRooms
     *
     * @return Plan
     */
    public function setMaxRooms($maxRooms)
    {
        $this->maxRooms = $maxRooms;

        return $this;
    }

    /**
     * Get maxRooms.
     *
     * @return int
     */
    public function getMaxRooms()
    {
        return $this->maxRooms;
    }

    /**
     * Set maxRoomsNotes.
     *
     * @param int $maxRoomsNotes
     *
     * @return Plan
     */
    public function setMaxRoomsNotes($maxRoomsNotes)
    {
        $this->maxRoomsNotes = $maxRoomsNotes;

        return $this;
    }

    /**
     * Get maxRoomsNotes.
     *
     * @return int
     */
    public function getMaxRoomsNotes()
    {
        return $this->maxRoomsNotes;
    }

    /**
     * Set isSharing.
     *
     * @param bool $isSharing
     *
     * @return Plan
     */
    public function setIsSharing($isSharing)
    {
        $this->isSharing = $isSharing;

        return $this;
    }

    /**
     * Get isSharing.
     *
     * @return bool
     */
    public function getIsSharing()
    {
        return $this->isSharing;
    }

    /**
     * Set isPublic.
     *
     * @param bool $isPublic
     *
     * @return Plan
     */
    public function setIsPublic($isPublic)
    {
        $this->isPublic = $isPublic;

        return $this;
    }
    
        /**
         * Get isPublic.
         *
         * @return bool
         */
        public function getIsPublic()
        {
            return $this->isPublic;
        }

    /**
     * Set isMonthly.
     *
     * @param bool $isMonthly
     *
     * @return Plan
     */
    public function setIsMonthly($isMonthly)
    {
        $this->isMonthly = $isMonthly;

        return $this;
    }

    /**
     * Get isMonthly.
     *
     * @return bool
     */
    public function getIsMonthly()
    {
        return $this->isMonthly;
    }

    /**
     * Set maxFilePerRoom.
     *
     * @param int $maxFilePerRoom
     *
     * @return Plan
     */
    public function setMaxFilePerRoom($maxFilePerRoom)
    {
        $this->maxFilePerRoom = $maxFilePerRoom;

        return $this;
    }

    /**
     * Get maxFilePerRoom.
     *
     * @return int
     */
    public function getMaxFilePerRoom()
    {
        return $this->maxFilePerRoom;
    }

    /**
     * Set maxFileSize.
     *
     * @param float $maxFileSize
     *
     * @return Plan
     */
    public function setMaxFileSize($maxFileSize)
    {
        $this->maxFileSize = $maxFileSize;

        return $this;
    }

    /**
     * Get maxFileSize.
     *
     * @return float
     */
    public function getMaxFileSize()
    {
        return $this->maxFileSize;
    }

    /**
     * Set price.
     *
     * @param float $price
     *
     * @return Plan
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price.
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return mixed
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param mixed $users
     */
    public function setUsers($users)
    {
        $this->users = $users;
    }
}
