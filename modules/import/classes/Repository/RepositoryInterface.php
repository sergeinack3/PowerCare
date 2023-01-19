<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Repository;

use Generator;
use Ox\Import\Framework\Entity\EntityInterface;

/**
 * External object repository
 */
interface RepositoryInterface
{
    /**
     * @param mixed $id
     *
     * @return EntityInterface|null
     */
    public function findById($id): ?EntityInterface;

    /**
     * @param int        $count
     * @param int        $offset
     * @param mixed|null $id
     *
     * @return Generator|null
     */
    public function get(int $count = 1, int $offset = 0, $id = null): ?Generator;

    /**
     * @param string $name Resource name
     * @param mixed  $id
     *
     * @return EntityInterface|null
     */
    public function findInPoolById(string $name, $id): ?EntityInterface;

    /**
     * @param string $name
     *
     * @return Generator|null
     */
    public function findCollectionInPool(string $name): ?Generator;
}
