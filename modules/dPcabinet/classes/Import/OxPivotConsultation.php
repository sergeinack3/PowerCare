<?php

/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet\Import;

use Ox\Import\GenericImport\AbstractOxPivotImportableObject;
use Ox\Import\GenericImport\FieldDescription;
use Ox\Import\GenericImport\GenericImport;
use Ox\Import\GenericImport\GenericPivotObject;

/**
 * Description
 */
class OxPivotConsultation extends AbstractOxPivotImportableObject implements GenericPivotObject
{
    public const FIELD_DATE             = 'date';
    public const FIELD_PRATICIEN        = 'praticien';
    public const FIELD_HEURE            = 'heure';
    public const FIELD_DUREE            = 'duree';
    public const FIELD_MOTIF            = 'motif';
    public const FIELD_REMARQUES        = 'remarques';
    public const FIELD_EXAMEN           = 'examen';
    public const FIELD_TRAITEMENT       = 'traitement';
    public const FIELD_HISTOIRE_MALADIE = 'histoire_maladie';
    public const FIELD_CONCLUSION       = 'conclusion';
    public const FIELD_RESULTATS        = 'resultats';
    public const FIELD_PATIENT          = 'patient';

    protected const FILE_NAME = GenericImport::CONSULTATION;

    protected function initFields(): void
    {
        if (!$this->importable_fields) {
            $this->importable_fields = [
                self::FIELD_ID               => $this->buildFieldId('Identifiant unique de la consultation'),
                self::FIELD_DATE             => $this->buildFieldDate(
                    self::FIELD_DATE,
                    'Date de la consultation',
                    true
                ),
                self::FIELD_PRATICIEN        => $this->buildFieldExternalId(
                    self::FIELD_PRATICIEN,
                    'Identifiant unique du praticien responsable de la consultation',
                    true
                ),
                self::FIELD_HEURE            => $this->buildFieldHeure(),
                self::FIELD_DUREE            => $this->buildFieldDuree(),
                self::FIELD_MOTIF            => $this->buildFieldLongText(
                    self::FIELD_MOTIF,
                    'Motif de la consultation',
                    true
                ),
                self::FIELD_REMARQUES        => $this->buildFieldLongText(
                    self::FIELD_REMARQUES,
                    'Remarques sur la consultation'
                ),
                self::FIELD_EXAMEN           => $this->buildFieldLongText(
                    self::FIELD_EXAMEN,
                    'Examens de la consultation'
                ),
                self::FIELD_TRAITEMENT       => $this->buildFieldLongText(
                    self::FIELD_TRAITEMENT,
                    'Traitements de la consultation'
                ),
                self::FIELD_HISTOIRE_MALADIE => $this->buildFieldLongText(
                    self::FIELD_HISTOIRE_MALADIE,
                    'Histoire de la maladie de la consultation'
                ),
                self::FIELD_CONCLUSION       => $this->buildFieldLongText(
                    self::FIELD_CONCLUSION,
                    'Conclusion de la consultation'
                ),
                self::FIELD_RESULTATS        => $this->buildFieldLongText(
                    self::FIELD_RESULTATS,
                    'Resultats de la consultation'
                ),
                self::FIELD_PATIENT          => $this->buildFieldExternalId(
                    self::FIELD_PATIENT,
                    'Identifiant unique du patient concerné par la consultation',
                    true
                ),
            ];
        }
    }

    private function buildFieldHeure(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_HEURE,
            8,
            FieldDescription::FIELD_TYPE_TIME . ' (ex: 09:00:00 pour 9h)',
            'Heure de début de la consultation',
            true
        );
    }

    private function buildFieldDuree(): FieldDescription
    {
        return new FieldDescription(
            'duree',
            8,
            FieldDescription::FIELD_TYPE_TIME . ' (ex: 00:15:00 pour 15min)',
            'Durée de la consultation (arrondi au quart d\'heure supérieur)'
        );
    }
}
