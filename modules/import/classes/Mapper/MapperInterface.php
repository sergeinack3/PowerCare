<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Mapper;

use Exception;
use Generator;
use Ox\Import\Framework\Adapter\AdapterInterface;
use Ox\Import\Framework\Entity\EntityInterface;

/**
 * Description
 */
interface MapperInterface
{
    /**
     * Get a single line from DB using $id
     *
     * @param mixed $id
     *
     * @return EntityInterface|null
     */
    public function retrieve($id): ?EntityInterface;

    /**
     * Get the n data from an optional Id
     *
     * @param int        $count
     * @param int        $offset
     * @param mixed|null $id
     *
     * @return Generator|null
     */
    public function get(int $count = 1, int $offset = 0, $id = null): ?Generator;

    public function count(): int;

    /**
     * @param MapperMetadata $metadata
     *
     * @return void
     */
    public function setMetadata(MapperMetadata $metadata): void;

    /**
     * @param AdapterInterface $adapter
     *
     * @return void
     */
    public function setAdapter(AdapterInterface $adapter): void;

    /**
     * @return MapperMetadata
     * @throws Exception
     */
    public function getMetadata(): MapperMetadata;

    /**
     * @return AdapterInterface
     * @throws Exception
     */
    public function getAdapter(): AdapterInterface;
}
