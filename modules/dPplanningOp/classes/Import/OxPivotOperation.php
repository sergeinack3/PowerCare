<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp\Import;

use Ox\Import\GenericImport\AbstractOxPivotImportableObject;
use Ox\Import\GenericImport\FieldDescription;
use Ox\Import\GenericImport\GenericImport;
use Ox\Import\GenericImport\GenericPivotObject;

/**
 * Pivot for operation import
 */
class OxPivotOperation extends AbstractOxPivotImportableObject implements GenericPivotObject
{
    public const FIELD_SEJOUR_ID = 'sejour';
    public const FIELD_CHIR_ID   = 'chir';
    public const FIELD_COTE      = 'cote';
    public const FIELD_DATE_TIME = 'date_intervention';
    public const FIELD_LIBELLE   = 'libelle';
    public const FIELD_EXAMEN    = 'examen';

    protected const FILE_NAME = GenericImport::OPERATION;

    protected function initFields(): void
    {
        if (!$this->importable_fields) {
            $this->importable_fields = [
                self::FIELD_ID        => $this->buildFieldId('Identifiant unique de l\'intervention'),
                self::FIELD_SEJOUR_ID => $this->buildFieldSejourId(),
                self::FIELD_CHIR_ID   => $this->buildFieldChirId(),
                self::FIELD_COTE      => $this->buildFieldCote(),
                self::FIELD_DATE_TIME => $this->buildFieldDatetime(),
                self::FIELD_LIBELLE   => $this->buildFieldLibelle(),
                self::FIELD_EXAMEN    => $this->buildFieldLongText(
                    self::FIELD_EXAMEN,
                    'Bilan pré-opératoire'
                ),
            ];
        }
    }

    private function buildFieldSejourId(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_SEJOUR_ID,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Identifiant unique du séjour',
            true
        );
    }

    private function buildFieldChirId(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_CHIR_ID,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Identifiant du chirurgien',
            true
        );
    }

    private function buildFieldCote(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_COTE,
            255,
            FieldDescription::FIELD_TYPE_STRING
            . ' : droit | gauche | haut | bas | bilatéral | total | inconnu | non_applicable',
            'Côté concerné par l\'intervention',
            true
        );
    }

    private function buildFieldLibelle(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_LIBELLE,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Libellé d\'intervention'
        );
    }

    private function buildFieldDatetime(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_DATE_TIME,
            20,
            FieldDescription::FIELD_TYPE_DATE_TIME,
            'Date et heure de l\'intervention'
        );
    }
}
