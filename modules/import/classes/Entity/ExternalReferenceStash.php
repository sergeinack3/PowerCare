<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Entity;

use Ox\Core\CStoredObject;

/**
 * Description
 */
class ExternalReferenceStash
{
    /** @var array */
    private $references = [];

    /**
     * @param ExternalReference $reference
     * @param CStoredObject     $mb_object
     *
     * @return void
     */
    public function addReference(ExternalReference $reference, CStoredObject $mb_object): void
    {
        $name = $reference->getName();

        if (!isset($this->references[$name])) {
            $this->references[$name] = [];
        }
        $this->references[$name][$reference->getId()] = $mb_object;
    }

    /**
     * @param string $name
     * @param mixed  $id
     *
     * @return CStoredObject|null
     */
    private function findMbByExternalId(string $name, $id): ?CStoredObject
    {
        return ($this->references[$name][$id]) ?? null;
    }

    /**
     * @param string $name
     * @param mixed  $id
     *
     * @return int|null
     */
    public function getMbIdByExternalId(string $name, $id = null): ?int
    {
        if ($id === null) {
            return null;
        }
        $mb_object = $this->findMbByExternalId($name, $id);

        if ($mb_object && $mb_object->_id) {
            return $mb_object->_id;
        }

        return null;
    }
}
