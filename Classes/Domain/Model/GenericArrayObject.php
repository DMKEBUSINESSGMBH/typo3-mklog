<?php

namespace DMK\Mklog\Domain\Model;

/***************************************************************
 * Copyright notice
 *
 * (c) 2020 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use DMK\Mklog\Factory;
use Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Basic data model with geter's and seter's.
 *
 * @author Michael Wagner
 */
class GenericArrayObject
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * @param array|GenericArrayObject $data
     *
     * @return GenericArrayObject
     */
    public static function getInstance($data = [])
    {
        if ($data instanceof self) {
            return $data;
        }
        if (!is_array($data)) {
            $data = [];
        }

        // create data instances recursive!
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = static::getInstance($value);
            }
        }

        // use get_called_class for backwards compatibility!
        return Factory::makeInstance(GenericArrayObject::class, $data);
    }

    /**
     * @param string|array $property
     * @param mixed        $value
     *
     * @return $this
     */
    public function setProperty($property, $value = null)
    {
        if (is_array($property)) {
            // wir Überschreiben den kompletten record
            $this->data = $property;

            return $this;
        }

        // wir setzen einen bestimmten wert
        $this->data[$property] = $value;

        return $this;
    }

    /**
     * @param string|mixed $property
     * @param mixed $default
     *
     * @return string|array
     */
    public function getProperty($property, $default = null)
    {
        return $this->hasProperty($property) ? $this->data[$property] : $default;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->data;
    }

    /**
     * @param string $property
     *
     * @return $this
     */
    public function unsProperty($property)
    {
        unset($this->data[$property]);

        return $this;
    }

    /**
     * @param string $property
     *
     * @return bool
     */
    public function hasProperty($property)
    {
        return isset($this->data[$property]);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->data);
    }

    /**
     * Set/Get attribute wrapper.
     *
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    public function __call($method, array $args)
    {
        $property = GeneralUtility::camelCaseToLowerCaseUnderscored(substr($method, 3));
        switch (substr($method, 0, 3)) {
            case 'get':
                return $this->getProperty($property);
            case 'set':
                return $this->setProperty($property, isset($args[0]) ? $args[0] : null);
            case 'uns':
                return $this->unsProperty($property);
            case 'has':
                return $this->hasProperty($property);
            default:
        }

        throw new Exception('Sorry, Invalid method "'.get_class($this).'::'.$method.'"', 1607447254);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = $this->getProperties();
        foreach ($array as $key => $value) {
            if ($value instanceof self) {
                $array[$key] = $value->toArray();
            }
        }

        return $array;
    }
}
