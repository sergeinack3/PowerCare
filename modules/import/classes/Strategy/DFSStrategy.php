<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Strategy;

use Ox\Core\CStoredObject;
use Ox\Import\Framework\Entity\EntityInterface;
use Ox\Import\Framework\Entity\ExternalReference;
use Ox\Import\Framework\Entity\File;
use Ox\Import\Framework\Exception\ImportException;
use Ox\Import\Framework\Exception\MandatoryFieldException;
use Ox\Import\Framework\Repository\GenericRepository;

/**
 * Depth-first search import strategy (object by object)
 */
class DFSStrategy extends AbstractStrategy
{
    /**
     * @inheritDoc
     */
    protected function importOne(EntityInterface $object, bool $reference = false): ?CStoredObject
    {
        // Get import entity
        $import_entity = $this->getImportEntity($object);

        $update = $this->getConfiguration()->offsetGet('update');

        $new = true;
        $mb_object = null;
        if ($this->isAlreadyImported($import_entity)) {
            $mb_object = $import_entity->getInternalObject();
            $object->setMbObject($mb_object);
            $new = false;
        }
        if ($update || !$mb_object) {
            try {
                // Check external object specs
                $this->checkViolations($object);

                // Import ref objects
                if ($_links = $object->getRefEntities()) {
                    $this->importRefObjects($_links);
                }

                // Transform to CStoredObject
                $mb_object = $object->transform(
                    $this->getTransformer(),
                    $this->getReferenceStash(),
                    $this->getCampaign()
                );

                // Do not match if mb_object already has an id
                if (!isset($mb_object->_id)) {
                    // Match an existing object
                    $mb_object = $mb_object->matchForImport($this->getMatcher());

                    if (isset($mb_object->_id) && $update) {
                        $object->setMbObject($mb_object);
                        // Transform to CStoredObject
                        $mb_object = $object->transform($this->getTransformer(), $this->getReferenceStash());
                    }
                }

                // Persist the object
                $mb_object = $mb_object->persistForImport($this->getPersister());
            } catch (ImportException $e) {
                // Log last error
                $this->getCampaign()->addImportedObject($object, null, $e->getMessage());

                $this->errors[] = $e->getMessage();
                $e->logError();

                return null;
            }
        }

        $this->messages[] = [
            "AbstractStrategy-Msg-%s successfully " . (($new) ? 'imported' : 'found'),
            UI_MSG_OK,
            get_class($object),
        ];

        if (!$reference) {
            $this->setLastExternalId($object->getExternalId());
        }

        try {
            $this->getCampaign()->addImportedObject($object, $mb_object);
        } catch (ImportException $e) {
            $e->logError();
        }

        $this->importCollections($object, $mb_object);

        return $mb_object;
    }

    /**
     * @param ExternalReference[] $links
     *
     * @return void
     * @throws MandatoryFieldException
     */
    private function importRefObjects(array $links): void
    {
        foreach ($links as $_link) {
            $mb_object = null;

            if ($_id = $_link->getId()) {
                $imported_entity = $this->getCampaign()->getImportedEntity(
                    GenericRepository::getExternalClassFromType($_link->getName()),
                    $_id
                );

                if ($imported_entity && $imported_entity->_id) {
                    $internal = $imported_entity->getInternalObject();
                    if ($internal && $internal->_id) {
                        $mb_object = $internal;
                    }
                }
            }

            if (!$mb_object || !$mb_object->_id) {
                $object = ($_link->getId()) ? $this->findInPoolByReference($_link) : null;

                if ($object) {
                    try {
                        $mb_object = $this->importOne($object, true);
                    } catch (ImportException $e) {
                        continue;
                    }
                } elseif ($_link->isMandatory()) {
                    throw new MandatoryFieldException('Ref is mandatory');
                }
            }

            if ($mb_object && $mb_object->_id) {
                $this->addExternalReferenceToStash($_link, $mb_object);
            }
        }
    }

    private function importCollections(EntityInterface $object, CStoredObject $mb_object): void
    {
        foreach ($object->getCollections() as $_collection_name => $_field) {
            foreach ($this->getRepository()->findCollectionInPool($_collection_name) as $_entity) {
                if (!$_entity->getExternalId()) {
                    break;
                }

                try {
                    $this->importOne($_entity, true);
                } catch (ImportException $e) {
                    continue;
                }
                //$this->addExternalReferenceToStash($_collection_name, $mb_object);
            }
        }
    }
}
