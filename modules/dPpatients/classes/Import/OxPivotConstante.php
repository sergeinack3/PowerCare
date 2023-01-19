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
class OxPivotConstante extends AbstractOxPivotImportableObject implements GenericPivotObject
{
    public const FIELD_PRATICIEN                     = 'praticien';
    public const FIELD_PATIENT                       = 'patient';
    public const FIELD_DATE                          = 'date';
    public const FIELD_TAILLE                        = 'taille';
    public const FIELD_POIDS                         = 'poids';
    public const FIELD_PULSE                         = 'pouls';
    public const FIELD_TEMPERATURE                   = 'temperature';
    public const FIELD_BLOOD_PRESSURE_SYSTOLE_RIGHT  = 'ta_droit_systole';
    public const FIELD_BLOOD_PRESSURE_DIASTOLE_RIGHT = 'ta_droit_diastole';
    public const FIELD_BLOOD_PRESSURE_SYSTOLE_LEFT   = 'ta_gauche_systole';
    public const FIELD_BLOOD_PRESSURE_DIASTOLE_LEFT  = 'ta_gauche_diastole';
    public const FIELD_SHOE_SIZE                     = 'pointure';

    protected const FILE_NAME = GenericImport::CONSTANTE;

    protected function initFields(): void
    {
        if (!$this->importable_fields) {
            $this->importable_fields = [
                self::FIELD_ID                            => $this->buildFieldId('Identifiant unique de la constante'),
                self::FIELD_PRATICIEN                     => $this->buildFieldExternalId(
                    self::FIELD_PRATICIEN,
                    'Identifiant unique du médecin responsable de la saisie de la constante'
                ),
                self::FIELD_PATIENT                       => $this->buildFieldExternalId(
                    self::FIELD_PATIENT,
                    'Identifiant unique du patient',
                    true
                ),
                self::FIELD_DATE                          => $this->buildFieldDate(
                    self::FIELD_DATE,
                    'Date et heure de saisie de la constante'
                ),
                self::FIELD_TAILLE                        => $this->buildFieldTaille(),
                self::FIELD_POIDS                         => $this->buildFieldPoids(),
                self::FIELD_PULSE                         => $this->buildFieldPulse(),
                self::FIELD_TEMPERATURE                   => $this->buildFieldTemperature(),
                self::FIELD_BLOOD_PRESSURE_SYSTOLE_RIGHT  => $this->buildFieldBloodPressureSystoleRight(),
                self::FIELD_BLOOD_PRESSURE_DIASTOLE_RIGHT => $this->buildFieldBloodPressureDiastoleRight(),
                self::FIELD_BLOOD_PRESSURE_SYSTOLE_LEFT   => $this->buildFieldBloodPressureSystoleLeft(),
                self::FIELD_BLOOD_PRESSURE_DIASTOLE_LEFT  => $this->buildFieldBloodPressureDiastoleLeft(),
                self::FIELD_SHOE_SIZE                     => $this->buildFieldShoeSize(),
            ];
        }
    }

    private function buildFieldTaille(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_TAILLE,
            5,
            FieldDescription::FIELD_TYPE_INT,
            'Taille du patient en centimètres (cm)'
        );
    }

    private function buildFieldPoids(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_TAILLE,
            10,
            FieldDescription::FIELD_TYPE_FLOAT,
            'Poids du patient en kilogrammes (Kg)'
        );
    }

    private function buildFieldPulse(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_PULSE,
            3,
            FieldDescription::FIELD_TYPE_INT,
            'Pouls du patient en pulsation par minute (puls./min) - Min: 20 / Max: 400'
        );
    }

    private function buildFieldTemperature(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_TEMPERATURE,
            10,
            FieldDescription::FIELD_TYPE_FLOAT,
            'Température du patient en Celsius (°C) - Min: 20 / Max: 50'
        );
    }

    private function buildFieldBloodPressureSystoleRight(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_BLOOD_PRESSURE_SYSTOLE_RIGHT,
            10,
            FieldDescription::FIELD_TYPE_FLOAT,
            'Tension artérielle systolique au bras droit du patient en centimètre de mercure (cmHg) - Max: 22'
        );
    }

    private function buildFieldBloodPressureDiastoleRight(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_BLOOD_PRESSURE_DIASTOLE_RIGHT,
            10,
            FieldDescription::FIELD_TYPE_FLOAT,
            'Tension artérielle diastolique au bras droit du patient en centimètre de mercure (cmHg) - Max: 15'
        );
    }

    private function buildFieldBloodPressureSystoleLeft(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_BLOOD_PRESSURE_SYSTOLE_LEFT,
            10,
            FieldDescription::FIELD_TYPE_FLOAT,
            'Tension artérielle systolique au bras gauche du patient en centimètre de mercure (cmHg) - Max: 22'
        );
    }

    private function buildFieldBloodPressureDiastoleLeft(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_BLOOD_PRESSURE_DIASTOLE_LEFT,
            10,
            FieldDescription::FIELD_TYPE_FLOAT,
            'Tension artérielle diastolique au bras gauche du patient en centimètre de mercure (cmHg) - Max: 15'
        );
    }

    private function buildFieldShoeSize(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_SHOE_SIZE,
            2,
            FieldDescription::FIELD_TYPE_INT,
            'Pointure de chaussure (Pointure FR) - Min: 15 / Max: 57'
        );
    }
}
