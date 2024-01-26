<?php

declare(strict_types=1);

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

namespace DMK\Mklog\Task;

use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Class OptimizeTableTask.
 *
 * @author  Mario Seidel
 * @license http://www.gnu.org/licenses/lgpl.html
 *          GNU Lesser General Public License, version 3 or later
 */
class CleanupLogTableTask extends AbstractTask
{
    /**
     * Cleanup devlog table and remove old entries.
     *
     * @return bool
     */
    public function execute()
    {
        $this->getDevlogEntryRepository()->optimize();

        return true;
    }

    /**
     * Returns the devlog entry repository.
     *
     * @return \DMK\Mklog\Domain\Repository\DevlogEntryRepository
     */
    protected function getDevlogEntryRepository()
    {
        return \DMK\Mklog\Factory::getDevlogEntryRepository();
    }
}
