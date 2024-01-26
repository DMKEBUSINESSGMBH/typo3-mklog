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

namespace DMK\Mklog\Backend;

/**
 * MK Log backend module.
 *
 * @author Michael Wagner
 */
class ModuleBackend extends \Sys25\RnBase\Backend\Module\BaseModule
{
    /**
     * Initializes the backend module by setting internal variables, initializing the menu.
     *
     * @SuppressWarnings(PHPMD.ShortMethodName)
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function init()
    {
        $this->MCONF = [
            'name' => 'web_MklogBackend',
        ];

        $this->getLanguageService()->includeLLFile('EXT:mklog/Resources/Private/Language/Backend.xlf');
        $this->getBackendUser()->modAccess($this->MCONF, 1);
        parent::init();
    }

    /**
     * Method to get the extension key.
     *
     * @return string Extension key
     */
    public function getExtensionKey()
    {
        return 'mklog';
    }
}
