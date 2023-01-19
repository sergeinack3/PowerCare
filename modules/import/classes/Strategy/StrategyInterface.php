<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Strategy;

use Ox\Import\Framework\Exception\ImportException;

/**
 * Description
 */
interface StrategyInterface
{
    /**
     * @param int        $count
     * @param int        $offset
     * @param mixed|null $id
     *
     * @return void
     * @throws ImportException
     */
    public function import(int $count = 1, int $offset = 0, $id = null): int;

    /**
     * Set the last treated external Id
     *
     * @param mixed $id
     *
     * @return void
     */
    public function setLastExternalId($id): void;

    /**
     * Get last treated external Id
     *
     * @return mixed
     */
    public function getLastExternalId();
}
