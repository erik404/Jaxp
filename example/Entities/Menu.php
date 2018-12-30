<?php
/**
 * Copyright (c) 2018 Erik-Jan van de Wal (ejvandewal@gmail.com)
 */

namespace erik404\Jaxp\example\Entities;

use erik404\Jaxp;

/**
 * Class Menu
 * @package erik404\example\Entities
 */
class Menu
{

    const XML_MAPPING = [
        Jaxp::KEY_PARENT_NODE => 'menu',
        Jaxp::KEY_CHILDREN => [
            'food' => [
                Jaxp::KEY_CLASS => 'erik404\Jaxp\example\Entities',
                Jaxp::KEY_SETTER => 'setParent',
            ]
        ],
        Jaxp::KEY_MAP => [
            'type' => 'setMenuType',
            'serving' => 'setMenuServingTime'
        ]
    ];

    private $menuType;
    private $menuServingTime;

    /**
     * @return mixed
     */
    public function getMenuType()
    {
        return $this->menuType;
    }

    /**
     * @param mixed $menuType
     */
    public function setMenuType($menuType)
    {
        $this->menuType = $menuType;
    }

    /**
     * @return mixed
     */
    public function getMenuServingTime()
    {
        return $this->menuServingTime;
    }

    /**
     * @param mixed $menuServingTime
     */
    public function setMenuServingTime($menuServingTime)
    {
        $this->menuServingTime = $menuServingTime;
    }



}