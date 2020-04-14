<?php
declare(strict_types=1);

namespace DMK\Mklog\Task;

use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Class OptimizeTableTask
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
