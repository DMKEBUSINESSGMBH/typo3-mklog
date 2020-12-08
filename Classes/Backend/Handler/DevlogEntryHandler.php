<?php

namespace DMK\Mklog\Backend\Handler;

/***************************************************************
 * Copyright notice
 *
 * (c) 2016 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

use DMK\Mklog\Backend\Repository\DevlogEntryRepository;

/**
 * Devlog Entry handler.
 *
 * @author Michael Wagner
 */
class DevlogEntryHandler extends \Tx_Rnbase_Backend_Handler_SearchHandler
{
    /**
     * Returns a unique ID for this handler.
     * This is used to created the subpart in template.
     *
     * @return string
     */
    public function getSubModuleId()
    {
        return 'mklog_devlog_entry';
    }

    /**
     * Returns the label for Handler in SubMenu. You can use a label-Marker.
     *
     * @return string
     */
    public function getSubLabel()
    {
        return '';
    }

    /**
     * The class for the searcher.
     *
     * @return string
     */
    protected function getListerClass()
    {
        return 'DMK\\Mklog\\Backend\\Lister\\DevlogEntryLister';
    }

    /**
     * Prepares the handler.
     */
    protected function prepare()
    {
        $options = $this->getOptions();

        $repo = new DevlogEntryRepository();
        $options->setBaseTableName($repo->getTableName());
    }
}
