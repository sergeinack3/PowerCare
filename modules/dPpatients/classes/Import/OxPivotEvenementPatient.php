<?php

/**
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
 * Generic Import - Generic
 * OxPivotEvenementPatient
 */
class OxPivotEvenementPatient extends AbstractOxPivotImportableObject implements GenericPivotObject
{
    public const FIELD_PATIENT      = 'patient_id';
    public const FIELD_PRACTITIONER = 'practitioner_id';
    public const FIELD_DATETIME     = 'datetime';
    public const FIELD_LABEL        = 'label';
    public const FIELD_TYPE         = 'type';
    public const FIELD_DESCRIPTION  = 'description';

    protected const FILE_NAME = GenericImport::EVENEMENT;

    protected function initFields(): void
    {
        if (!$this->importable_fields) {
            $this->importable_fields = [
                self::FIELD_ID           => $this->buildFieldId(
                    'Identifiant unique de l\'événement'
                ),
                self::FIELD_PATIENT      => $this->buildFieldPatient(),
                self::FIELD_PRACTITIONER => $this->buildFieldPractitioner(),
                self::FIELD_DATETIME     => $this->buildFieldDatetime(),
                self::FIELD_LABEL        => $this->buildFieldLabel(),
                self::FIELD_TYPE         => $this->buildFieldType(),
                self::FIELD_DESCRIPTION  => $this->buildFieldDescription(),
            ];
        }
    }

    /**
     * Build patient_id field
     *
     * @return FieldDescription
     */
    private function buildFieldPatient(): FieldDescription
    {
        return $this->buildFieldExternalId(
            self::FIELD_PATIENT,
            'Identifiant unique du patient associé à l\'événement',
            true
        );
    }

    /**
     * Build practitioner_id field
     *
     * @return FieldDescription
     */
    private function buildFieldPractitioner(): FieldDescription
    {
        return $this->buildFieldExternalId(
            self::FIELD_PRACTITIONER,
            'Identifiant unique du médecin responsable associé à l\'événement',
            true
        );
    }

    /**
     * Build datetime field
     *
     * @return FieldDescription
     */
    private function buildFieldDatetime(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_DATETIME,
            20,
            FieldDescription::FIELD_TYPE_DATE_TIME,
            'Date + heure de l\'événement',
            true
        );
    }

    /**
     * Build label field
     *
     * @return FieldDescription
     */
    private function buildFieldLabel(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_LABEL,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Libellé de l\'événement',
            true
        );
    }

    /**
     * Build type field
     *
     * @return FieldDescription
     */
    private function buildFieldType(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_TYPE,
            80,
            'sejour = événement de type séjour, intervention = événement de type intervention,'
            . ' evt = événement non spécifique',
            'Type de l\'événement',
            false
        );
    }

    /**
     * Build description field
     *
     * @return FieldDescription
     */
    private function buildFieldDescription(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_DESCRIPTION,
            65535,
            FieldDescription::FIELD_TYPE_STRING,
            'Description de l\'événement',
            false
        );
    }
}
