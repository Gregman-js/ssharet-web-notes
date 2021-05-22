<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RoomMember
 *
 * @ORM\Table(name="room_member")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RoomMemberRepository")
 */
class RoomMember
{
    public static $STATUS_SEND = "send";
    public static $STATUS_APPROVE = "approve";
    public static $STATUS_REFUSE = "refuse";
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Room", inversedBy="members")
     * @ORM\JoinColumn(name="room_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $room;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="roomMembers")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="write_right", type="boolean")
     */
    private $writeRight = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sendDate", type="datetime")
     */
    private $sendDate;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="reciveDate", type="datetime", nullable=true)
     */
    private $reciveDate;


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
     * Set status.
     *
     * @param string $status
     *
     * @return RoomMember
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set writeRight.
     *
     * @param boolean $writeRight
     *
     * @return RoomMember
     */
    public function setWriteRight($writeRight)
    {
        $this->writeRight = $writeRight;

        return $this;
    }

    /**
     * Get writeRight.
     *
     * @return bool
     */
    public function getWriteRight()
    {
        return $this->writeRight;
    }

    /**
     * Set sendDate.
     *
     * @param \DateTime $sendDate
     *
     * @return RoomMember
     */
    public function setSendDate($sendDate)
    {
        $this->sendDate = $sendDate;

        return $this;
    }

    /**
     * Get sendDate.
     *
     * @return \DateTime
     */
    public function getSendDate()
    {
        return $this->sendDate;
    }

    /**
     * Set reciveDate.
     *
     * @param \DateTime|null $reciveDate
     *
     * @return RoomMember
     */
    public function setReciveDate($reciveDate = null)
    {
        $this->reciveDate = $reciveDate;

        return $this;
    }

    /**
     * Get reciveDate.
     *
     * @return \DateTime|null
     */
    public function getReciveDate()
    {
        return $this->reciveDate;
    }
}
