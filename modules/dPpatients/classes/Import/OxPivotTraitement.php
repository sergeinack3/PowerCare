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
class OxPivotTraitement extends AbstractOxPivotImportableObject implements GenericPivotObject
{
    public const FIELD_PRATICIEN  = 'praticien';
    public const FIELD_PATIENT    = 'patient';
    public const FIELD_TEXT       = 'text';
    public const FIELD_DATE_DEBUT = 'date_debut';
    public const FIELD_DATE_FIN   = 'date_fin';

    protected const FILE_NAME = GenericImport::TRAITEMENT;

    protected function initFields(): void
    {
        if (!$this->importable_fields) {
            $this->importable_fields = [
                self::FIELD_ID         => $this->buildFieldId('Identifiant unique du traitement'),
                self::FIELD_PRATICIEN  => $this->buildFieldExternalId(
                    self::FIELD_PRATICIEN,
                    'Identifiant unique du médecin responsable du traitement'
                ),
                self::FIELD_PATIENT    => $this->buildFieldExternalId(
                    self::FIELD_PATIENT,
                    'Identifiant unique du patient',
                    true
                ),
                self::FIELD_TEXT       => $this->buildFieldLongText(self::FIELD_TEXT, 'Texte de l\'antécédent', true),
                self::FIELD_DATE_DEBUT => $this->buildFieldDate(self::FIELD_DATE_DEBUT, 'Date de début du traitement'),
                self::FIELD_DATE_FIN   => $this->buildFieldDate(self::FIELD_DATE_FIN, 'Date de fin du traitement'),
            ];
        }
    }
}
