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
                    'Identifiant unique de l\'�v�nement'
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
            'Identifiant unique du patient associ� � l\'�v�nement',
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
            'Identifiant unique du m�decin responsable associ� � l\'�v�nement',
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
            'Date + heure de l\'�v�nement',
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
            'Libell� de l\'�v�nement',
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
            'sejour = �v�nement de type s�jour, intervention = �v�nement de type intervention,'
            . ' evt = �v�nement non sp�cifique',
            'Type de l\'�v�nement',
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
            'Description de l\'�v�nement',
            false
        );
    }
}
