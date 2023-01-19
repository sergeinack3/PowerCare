<?php

/**
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport\Mapper;

use Ox\Import\Framework\Entity\DossierMedical;
use Ox\Import\Framework\Entity\EntityInterface;
use Ox\Import\Framework\Mapper\AbstractMapper;
use Ox\Mediboard\Patients\Import\OxPivotDossierMedical;

/**
 * Map medical informations from generic import format to DossierMedical Object
 */
class DossierMedicalMapper extends AbstractMapper
{
    /**
     * @inheritDoc
     */
    protected function createEntity($row): EntityInterface
    {
        $map = [
            'external_id'   => $this->getValue($row, OxPivotDossierMedical::FIELD_ID),
            'patient_id'    => $this->getValue($row, OxPivotDossierMedical::FIELD_PATIENT),
            'group_sanguin' => $this->getValue($row, OxPivotDossierMedical::FIELD_GROUP_SANGUIN),
            'rhesus'        => $this->getValue($row, OxPivotDossierMedical::FIELD_RHESUS),
        ];

        return DossierMedical::fromState($map);
    }
}
