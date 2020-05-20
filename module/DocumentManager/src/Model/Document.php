<?php

namespace DocumentManager\Model;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use DomainException;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;


/**
 * Documents
 *
 * @ORM\Table(name="Documents")
 * @ORM\Entity
 */
class Document
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    public $id;

    /**
     * @var string
     *
     * @ORM\Column(name="displayName", type="string", length=100, nullable=true)
     */
    public $displayName;


    /**
     * @var string
     *
     * @ORM\Column(name="storageName", type="string", length=100, nullable=true)
     */
    public $storageName;

    /**
     * @var datetime
     *
     * @ORM\Column(name="date", type="datetime", length=100, nullable=true)
     */
    public $date;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isDIR", type="boolean")
     */
    public $isDIR;

    /**
     * @var integer
     *
     * @ORM\Column(name="version", type="integer", nullable=false)
     *
     */
    public $version;

    /**
     * @var integer
     *
     * @ORM\Column(name="parentId", type="integer" , nullable=true)
     *
     */
    public $parentId;

    /**
     * @var integer
     *
     * @ORM\Column(name="ownerId", type="integer" , nullable=true)
     *
     */
    public $ownerId;

    private $inputFilter;


    public function exchangeArray(array $data)
    {
        $d = new DateTime();
        $d->format('Y-m-d H:i:s');
        $this->id = !empty($data['id']) ? $data['id'] : null;
        $this->displayName = !empty($data['displayName']) ? $data['displayName'] : 'New Node';
        $this->storageName = !empty($data['storageName']) ? $data['storageName'] : 'New Node';
        $this->date = !empty($data['date']) ? $data['date'] : $d;
        $this->isDIR = !empty($data['isDIR']) ? $data['isDIR'] : '0';
        $this->version = !empty($data['version']) ? $data['version'] : '0';
        $this->parentId = !empty($data['parentId']) ? $data['parentId'] : '0';
        $this->ownerId = !empty($data['ownerId']) ? $data['ownerId'] : '0';
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


    /**
     * @return bool
     */
    public function isDIR()
    {
        return $this->isDIR;
    }

    /**
     * @param bool $isDIR
     */
    public function setIsDIR($isDIR)
    {
        $this->isDIR = $isDIR;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param int $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param int $parentId
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
    }

    /**
     * @return int
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * @param int $ownerId
     */
    public function setOwnerId($ownerId)
    {
        $this->ownerId = $ownerId;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @param string $displayName
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
    }

    /**
     * @return string
     */
    public function getStorageName()
    {
        return $this->storageName;
    }

    /**
     * @param string $storageName
     */
    public function setStorageName($storageName)
    {
        $this->storageName = $storageName;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param string $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }
}