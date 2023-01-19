<?php

/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Mediusers\Import;

use Ox\Import\GenericImport\AbstractOxPivotImportableObject;
use Ox\Import\GenericImport\FieldDescription;
use Ox\Import\GenericImport\GenericImport;
use Ox\Import\GenericImport\GenericPivotObject;

/**
 * Description
 */
class OxPivotMediuser extends AbstractOxPivotImportableObject implements GenericPivotObject
{
    public const FIELD_NOM    = 'nom';
    public const FIELD_PRENOM = 'prenom';

    protected const FILE_NAME = GenericImport::UTILISATEUR;

    protected function initFields(): void
    {
        if (!$this->importable_fields) {
            $this->importable_fields = [
                self::FIELD_ID     => $this->buildFieldId('Identifiant unique de l\'utilisateur'),
                self::FIELD_NOM    => $this->buildFieldNom(),
                self::FIELD_PRENOM => $this->buildFieldPrenom(),
            ];
        }
    }

    private function buildFieldNom(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_NOM,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Nom de l\'utilisateur',
            true
        );
    }

    private function buildFieldPrenom(): FieldDescription
    {
        return new FieldDescription(
            self::FIELD_PRENOM,
            255,
            FieldDescription::FIELD_TYPE_STRING,
            'Prénom de l\'utilisateur'
        );
    }
}
