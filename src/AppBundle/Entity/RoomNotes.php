<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RoomNotes
 *
 * @ORM\Table(name="room_notes")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RoomNotesRepository")
 */
class RoomNotes
{
    public function __construct()
    {
        $this->edited = new \DateTime("now");
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
     * @ORM\ManyToOne(targetEntity="Room", inversedBy="notes")
     * @ORM\JoinColumn(name="room_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $room;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="roomNotes")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title = "New Note";

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content = "";

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="edited", type="datetime")
     */
    private $edited;



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
     * @return int
     */
    public function getRoom()
    {
        return $this->room;
    }

    /**
     * @param int $room
     */
    public function setRoom($room)
    {
        $this->room = $room;
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
    }


    /**
     * Set title.
     *
     * @param string $title
     *
     * @return RoomNotes
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set content.
     *
     * @param string $content
     *
     * @return RoomNotes
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
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
}
