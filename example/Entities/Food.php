<?php
/**
 * Copyright (c) 2018 Erik-Jan van de Wal (ejvandewal@gmail.com)
 */

namespace erik404\Jaxp\example\Entities;

use erik404\Jaxp;

/**
 * Class Food
 * @package erik404\example\Entities
 */
class Food
{
    const XML_MAPPING = [
        Jaxp::KEY_PARENT_NODE => 'menu',
        Jaxp::KEY_MAP => [
            'name' => 'setName',
            'price' => 'setPrice',
            'description' => 'setDescription',
            'calories' => 'setCalories'
        ]
    ];


    private $name;
    private $price;
    private $description;
    private $calories;
    private $parent;

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param mixed $parent
     */
    public function setParent($parent): void
    {
        $this->parent = $parent;
    }
    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getCalories()
    {
        return $this->calories;
    }

    /**
     * @param mixed $calories
     */
    public function setCalories($calories)
    {
        $this->calories = $calories;
    }

}