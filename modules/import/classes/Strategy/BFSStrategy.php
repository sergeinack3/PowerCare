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
use Ox\Import\Framework\Exception\ImportException;
use Ox\Import\Framework\Exception\MandatoryFieldException;
use Ox\Import\Framework\Repository\GenericRepository;
use Ox\Mediboard\Cabinet\CPlageconsult;

/**
 * Breadth-first search strategy (entity by entity)
 */
class BFSStrategy extends AbstractStrategy
{
    /**
     * @inheritDoc
     */
    protected function importOne(EntityInterface $object, bool $reference = false): ?CStoredObject
    {
        if (!$object->getExternalId()) {
            return null;
        }

        $update = false;
        if (($config = $this->getConfiguration()) && $config->offsetExists('update')) {
            $update = (bool)$config->offsetGet('update');
        }

        // Get import entity
        $import_entity = $this->getImportEntity($object);

        $new = true;
        if ($this->isAlreadyImported($import_entity)) {
            $new       = false;
            $mb_object = $import_entity->getInternalObject();
            $object->setMbObject($mb_object);
        }

        if ($update || !isset($mb_object)) {
            try {
                // Check external object specs
                $this->checkViolations($object);

                // Import ref objects
                if ($_links = $object->getRefEntities()) {
                    $this->importRefObjects($_links);
                }

                // Transform to CStoredObject
                $mb_object = $object->transform($this->getTransformer(), $this->getReferenceStash());

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

        if ($new) {
            try {
                $this->getCampaign()->addImportedObject($object, $mb_object);
            } catch (ImportException $e) {
                $this->errors[] = $e->getMessage();
            }
        }

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
            if ($_id = $_link->getId()) {
                $imported_entity = $this->getCampaign()->getImportedEntity(
                    ($this->getRepository())::getExternalClassFromType($_link->getName()),
                    $_id
                );

                if ($imported_entity && $imported_entity->_id && ($internal = $imported_entity->getInternalObject())) {
                    if ($internal && $internal->_id) {
                        $this->addExternalReferenceToStash($_link, $internal);
                    }
                } else {
                    $object = $this->findInPoolByReference($_link);

                    if ($object) {
                        try {
                            $mb_object = $this->importOne($object, true);
                        } catch (ImportException $e) {
                            continue;
                        }
                        if ($mb_object && $mb_object->_id) {
                            $this->addExternalReferenceToStash($_link, $mb_object);
                        }
                    } elseif ($_link->isMandatory()) {
                        throw new MandatoryFieldException("Ref {$_link->getName()} is mandatory");
                    }
                }
            }
        }
    }
}

