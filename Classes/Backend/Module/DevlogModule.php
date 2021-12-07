<?php

/*
 * Copyright notice
 *
 * (c) 2011-2021 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

namespace DMK\Mklog\Backend\Module;

/**
 * Devlog module.
 *
 * @author Michael Wagner
 */
class DevlogModule extends \Sys25\RnBase\Backend\Module\ExtendedModFunc
{
    /**
     * Method getFuncId.
     *
     * @return string
     */
    protected function getFuncId()
    {
        return 'mklog_devlog';
    }

    /**
     * Returns all sub handlers.
     *
     * @return array
     */
    protected function getSubMenuItems()
    {
        return [
            \tx_rnbase::makeInstance(
                'DMK\\Mklog\\Backend\\Handler\\DevlogEntryHandler'
            ),
        ];
    }

    /**
     * Liefert false, wenn es keine SubSelectors gibt.
     * sonst ein Array mit den ausgewählten Werten.
     *
     * @param string $selectorStr
     *
     * @return array or false if not needed. Return empty array if no item found
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function makeSubSelectors(&$selectorStr)
    {
        return false;
    }
}
