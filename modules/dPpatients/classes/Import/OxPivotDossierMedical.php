<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Import;

use Ox\Import\GenericImport\AbstractOxPivotImportableObject;
use Ox\Import\GenericImport\FieldDescription;
use Ox\Import\GenericImport\GenericImport;
use Ox\Import\GenericImport\GenericPivotObject;

/**
 * Description
 */
class OxPivotDossierMedical extends AbstractOxPivotImportableObject implements GenericPivotObject
{
    public const FIELD_PATIENT       = 'patient';
    public const FIELD_GROUP_SANGUIN = 'groupe_sanguin';
    public const FIELD_RHESUS        = 'rhesus';

    protected const FILE_NAME = GenericImport::DOSSIER_MEDICAL;

    protected function initFields(): void
    {
        if (!$this->importable_fields) {
            $this->importable_fields = [
                self::FIELD_ID        => $this->buildFieldId('Identifiant unique du dossier médical'),
                self::FIELD_PATIENT   => $this->buildFieldExternalId(
                    self::FIELD_PATIENT,
                    'Identifiant unique du patient',
                    true
                ),
                self::FIELD_GROUP_SANGUIN    => $this->buildFieldGroupSanguin(),
                self::FIELD_RHESUS     => $this->buildFieldRhesus(),
            ];
        }
    }

    private function buildFieldGroupSanguin(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_GROUP_SANGUIN,
            2,
            'Une valeur parmis A, B, O, AB',
            'Groupe sanguin du patient'
        );
    }

    private function buildFieldRhesus(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_RHESUS,
            3,
            'Une valeur parmis POS, NEG',
            'Rhesus du patient'
        );
    }
}
