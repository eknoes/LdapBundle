<?php
/**
 * Created by PhpStorm.
 * User: mdv
 * Date: 11.05.20
 * Time: 11:09
 */

namespace con4gis\AuthBundle\Entity;

use \Doctrine\ORM\Mapping as ORM;

/**
 * Class Service
 *
 * @ORM\Entity
 * @ORM\Table(name="tl_c4g_auth_be_groups")
 * @package con4gis\AuthBundle\Entity
 */
class Con4gisAuthBackendGroups
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="id", type="integer")
     */
    protected $id = 0;

    /**
     * @var int
     * @ORM\Column(name="tstamp", type="integer")
     */
    protected $tstamp = '';

    /**
     * @var string
     * @ORM\Column(name="filter", type="string")
     */
    protected $filter = '';

    /**
     * @var string
     * @ORM\Column(name="groups", type="blob")
     */
    protected $groups = '';

    /**
     * @var string
     * @ORM\Column(name="adminGroup", type="string")
     */
    protected $adminGroup = 0;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getFilter(): string
    {
        return $this->filter ? $this->filter : '';
    }

    /**
     * @param int $filter
     */
    public function setFilter(int $filter)
    {
        $this->filter = $filter;
    }

    /**
     * @return string
     */
    public function getTstamp(): int
    {
        return $this->tstamp ? $this->tstamp : '';
    }

    /**
     * @param int $tstamp
     */
    public function setTstamp(int $tstamp)
    {
        $this->tstamp = $tstamp;
    }

    /**
     * @return string
     */
    public function getGroups(): string
    {
        return $this->groups ? $this->groups : '';
    }

    /**
     * @param int $groups
     */
    public function setGroups(int $groups)
    {
        $this->groups = $groups;
    }

    /**
     * @return string
     */
    public function getAdminGroup(): string
    {
        return $this->adminGroup ? $this->adminGroup : '';
    }

    /**
     * @param string $adminGroup
     */
    public function setAdminGroup(string $adminGroup)
    {
        $this->adminGroup = $adminGroup;
    }
}