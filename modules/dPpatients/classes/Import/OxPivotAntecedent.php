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
class OxPivotAntecedent extends AbstractOxPivotImportableObject implements GenericPivotObject
{
    public const FIELD_PRATICIEN = 'praticien';
    public const FIELD_PATIENT   = 'patient';
    public const FIELD_TEXT      = 'text';
    public const FIELD_COMMENT   = 'comment';
    public const FIELD_DATE      = 'date';
    public const FIELD_TYPE      = 'type';
    public const FIELD_APPAREIL  = 'appareil';

    protected const FILE_NAME = GenericImport::ANTECEDENT;

    protected function initFields(): void
    {
        if (!$this->importable_fields) {
            $this->importable_fields = [
                self::FIELD_ID        => $this->buildFieldId('Identifiant unique de l\'antécédent'),
                self::FIELD_PRATICIEN => $this->buildFieldExternalId(
                    self::FIELD_PRATICIEN,
                    'Identifiant unique du médecin responsable de l\'antécédent'
                ),
                self::FIELD_PATIENT   => $this->buildFieldExternalId(
                    self::FIELD_PATIENT,
                    'Identifiant unique du patient',
                    true
                ),
                self::FIELD_TEXT      => $this->buildFieldLongText(self::FIELD_TEXT, 'Texte de l\'antécédent', true),
                self::FIELD_COMMENT   => $this->buildFieldLongText(
                    self::FIELD_COMMENT,
                    'Commentaire sur l\'antécédent'
                ),
                self::FIELD_DATE      => $this->buildFieldDate(self::FIELD_DATE, 'Date de début de l\'antécédent'),
                self::FIELD_TYPE      => $this->buildFieldType(),
                self::FIELD_APPAREIL  => $this->buildFieldAppareil(),
            ];
        }
    }

    private function buildFieldType(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_TYPE,
            80,
            'med = médical, alle = allergie, obst = obstétrique, chir = chirurgical, fam = familial',
            'Type d\'antécédent'
        );
    }

    private function buildFieldAppareil(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_APPAREIL,
            80,
            'cardiovasculaire, digestif, endocrinien, neuro_psychiatrique,
            pulmonaire, uro_nephrologique, orl, gyneco_obstetrique, orthopedique,
            ophtalmologique, locomoteur, terrain, neuro, divers, cancero',
            'Appareil de l\'antécédent'
        );
    }
}
