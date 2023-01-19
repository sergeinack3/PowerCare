<?php

/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Resolver;

use Ox\Core\CStoredObject;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Eai\Repository\ConsultationRepository;
use Ox\Interop\Eai\Repository\ObjectRepository;
use Ox\Interop\Eai\Repository\OperationRepository;
use Ox\Interop\Eai\Repository\SejourRepository;
use Ox\Interop\Sas\CSAS;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * trait CSejourTrait
 * Sejour utilities EAI
 */
class FileTargetResolver
{
    private bool $mode_sas = false;
    private ?string $target_class = null;
    private ?CPatient $patient = null;
    private ?CInteropActor $actor = null;
    private ?string $id400_category = null;
    private ?SejourRepository $sejour_repository = null;
    private ?OperationRepository $operation_repository = null;
    private ?ConsultationRepository $consultation_repository = null;

    /**
     * @param bool $is_sas
     *
     * @return FileTargetResolver
     */
    public function setModeSas(bool $is_sas): FileTargetResolver
    {
        $this->mode_sas = $is_sas;

        return $this;
    }

    /**
     * @param CInteropActor $actor
     * @param string        $target_class
     *
     * @return CStoredObject|null
     */
    public function resolve(CInteropActor $actor, string $target_class): ?CStoredObject
    {
        $this->target_class = $target_class;
        $this->actor        = $actor;

        if ($this->mode_sas) {
            return null;
        }

        if (!$this->patient) {
            return null;
        }

        if ($this->target_class === "CFilesCategory") {
            $this->target_class = $this->resolveFileCategoryTarget();
        }

        if ($this->target_class === 'CPatient') {
            return $this->patient;
        } elseif ($this->target_class === 'CSejour' && $this->sejour_repository) {
            return $this->sejour_repository->getOrFind();
        } elseif ($this->target_class === 'COperation' && $this->operation_repository) {
            return $this->operation_repository->getOrFind();
        } elseif ($this->target_class === 'CConsultation' && $this->consultation_repository) {
            return $this->consultation_repository->getOrFind();
        } else {
            /** @var ObjectRepository[] $ordered_locators */
            $ordered_locators = [
                $this->consultation_repository,
                $this->operation_repository,
                $this->sejour_repository,
            ];
            foreach ($ordered_locators as $locator) {
                if ($locator && ($target = $locator->getOrFind())) {
                    return $target;
                }
            }
        }

        return null;
    }

    /**
     * @param CPatient|null $patient
     *
     * @return FileTargetResolver
     */
    public function setPatient(?CPatient $patient): FileTargetResolver
    {
        $this->patient = $patient;

        return $this;
    }

    private function resolveFileCategoryTarget(): string
    {
        if (!$this->id400_category) {
            return $this->target_class;
        }

        // Chargement de la catégorie par son idex
        $group_id = $this->actor->group_id;
        $idex     = CIdSante400::getMatch(
            "CFilesCategory",
            CSAS::getFilesCategoryAssociationTag($group_id),
            $this->id400_category
        );

        $files_category = new CFilesCategory();
        if ($idex->_id) {
            $files_category->load($idex->object_id);
            if (!$files_category->_id) {
                return $this->target_class; // todo null ou error ?
            }
        }

        switch ($files_category->class) {
            case "CPatient":
            case "CSejour":
            case "COperation":
                $object_attach_OBX = $files_category->class;
                break;

            default:
                $object_attach_OBX = "CMbObject";
                break;
        }

        return $object_attach_OBX;
    }

    /**
     * @param string|null $id400_category
     *
     * @return FileTargetResolver
     */
    public function setId400Category(?string $id400_category): FileTargetResolver
    {
        $this->id400_category = $id400_category;

        return $this;
    }

    /**
     * @param ConsultationRepository|null $consultation_repository
     *
     * @return FileTargetResolver
     */
    public function setConsultationRepository(?ConsultationRepository $consultation_repository): FileTargetResolver
    {
        $this->consultation_repository = $consultation_repository;

        return $this;
}

    /**
     * @param OperationRepository|null $operation_repository
     *
     * @return FileTargetResolver
     */
    public function setOperationRepository(?OperationRepository $operation_repository): FileTargetResolver
    {
        $this->operation_repository = $operation_repository;

        return $this;
}

    /**
     * @param SejourRepository|null $sejour_repository
     *
     * @return FileTargetResolver
     */
    public function setSejourRepository(?SejourRepository $sejour_repository): FileTargetResolver
    {
        $this->sejour_repository = $sejour_repository;

        return $this;
}
}
