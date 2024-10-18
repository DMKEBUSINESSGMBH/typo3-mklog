<?php

/*
 * Copyright notice
 *
 * (c) 2011-2024 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
 * All rights reserved
 *
 * This file is part of the "mklog" Extension for TYPO3 CMS.
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GNU Lesser General Public License can be found at
 * www.gnu.org/licenses/lgpl.html
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

namespace DMK\Mklog\Domain\Model;

use DMK\Mklog\Factory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Basic data model with geter's and seter's.
 *
 * @author Michael Wagner
 */
class GenericArrayObject
{
    public function __construct(private array $data = [])
    {
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
     *
     * @return $this
     */
    public function setProperty($property, $value = null): static
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
     *
     * @return string|array
     */
    public function getProperty($property, $default = null)
    {
        return $this->hasProperty($property) ? $this->data[$property] : $default;
    }

    public function getProperties(): array
    {
        return $this->data;
    }

    /**
     * @param string $property
     *
     * @return $this
     */
    public function unsProperty($property): static
    {
        unset($this->data[$property]);

        return $this;
    }

    /**
     * @param string $property
     */
    public function hasProperty($property): bool
    {
        return isset($this->data[$property]);
    }

    public function isEmpty(): bool
    {
        return [] === $this->data;
    }

    /**
     * Set/Get attribute wrapper.
     */
    public function __call(string $method, array $args)
    {
        $property = GeneralUtility::camelCaseToLowerCaseUnderscored(substr($method, 3));

        return match (substr($method, 0, 3)) {
            'get' => $this->getProperty($property),
            'set' => $this->setProperty($property, $args[0] ?? null),
            'uns' => $this->unsProperty($property),
            'has' => $this->hasProperty($property),
            default => throw new \Exception('Sorry, Invalid method "'.static::class.'::'.$method.'"', 1607447254),
        };
    }

    public function toArray(): array
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
