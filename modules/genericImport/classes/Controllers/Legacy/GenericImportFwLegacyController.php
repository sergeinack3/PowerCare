<?php

/**
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport\Controllers\Legacy;

use Ox\Import\Framework\CFwImport;
use Ox\Import\Framework\Controllers\Legacy\CImportFwLegacyController;
use Ox\Import\GenericImport\GenericImport;
use Ox\Import\GenericImport\GenericImportSql;

/**
 * Legacy controller for generic import
 */
class GenericImportFwLegacyController extends CImportFwLegacyController
{
    public function configure(): void
    {
        $this->checkPermAdmin();

        $this->renderSmarty('configure');
    }


    protected function getModName(): string
    {
        return "genericImport";
    }

    protected function getImportInstance(?string $type = null): CFwImport
    {
        switch ($type) {
            case GenericImportLegacyController::IMPORT_CSV:
            default:
                return new GenericImport();
            case GenericImportLegacyController::IMPORT_SQL:
                return new GenericImportSql();
        }
    }

    protected function getUsersTable(): string
    {
        return 'utilisateur';
    }

    protected function isImportBypatient(string $type): bool
    {
        switch ($type) {
            case 'patient':
            case 'correspondant_medical':
            case 'antecedent':
            case 'traitement':
            case 'consultation':
            case 'evenement':
            case 'fichier':
            case 'constante':
            case 'sejour':
            case 'dossier_medical':
                return true;
            case 'utilisateur':
            case 'medecin':
            case 'plage_consultation':
            case 'acte_ccam':
            case 'acte_ngap':
            default:
                return false;
        }
    }
}
