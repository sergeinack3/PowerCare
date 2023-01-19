<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Import;

use Ox\Import\GenericImport\AbstractOxPivotImportableObject;
use Ox\Import\GenericImport\GenericImport;
use Ox\Import\GenericImport\GenericPivotObject;

/**
 * Description
 */
class OxPivotCorrespondant extends AbstractOxPivotImportableObject implements GenericPivotObject
{
    public const FIELD_MEDECIN = 'medecin';
    public const FIELD_PATIENT = 'patient';

    protected const FILE_NAME = GenericImport::CORRESPONDANT_MEDICAL;

    protected function initFields(): void
    {
        if (!$this->importable_fields) {
            $this->importable_fields = [
                self::FIELD_ID      => $this->buildFieldId('Identifiant unique de la correspondance'),
                self::FIELD_MEDECIN => $this->buildFieldExternalId(
                    self::FIELD_MEDECIN,
                    'Identifiant unique du médecin concerné',
                    true
                ),
                self::FIELD_PATIENT => $this->buildFieldExternalId(
                    self::FIELD_PATIENT,
                    'Identifiant unique du patient concerné',
                    true
                ),
            ];
        }
    }
}
