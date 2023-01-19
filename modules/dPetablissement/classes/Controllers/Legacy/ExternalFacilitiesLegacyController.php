<?php

/**
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Etablissement\Controllers\Legacy;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Core\Sessions\CSessionHandler;
use Ox\Mediboard\Etablissement\ExternalFacilitiesImporter;

class ExternalFacilitiesLegacyController extends CLegacyController
{
    /**
     * @throws CMbException
     * @throws Exception
     */
    public function importExternalFacilities(): void
    {
        $this->checkPermAdmin();

        CMbObject::$useObjectCache = false;

        CApp::setTimeLimit(3600);
        CSessionHandler::writeClose();

        $form_file = CValue::files('formfile');

        if (!$form_file) {
            return;
        }
        $importer = new ExternalFacilitiesImporter($form_file);
        $importer->doImport();

        $result = $importer->getImportResult();
        $count_created = $result["created"];
        $count_updated = $result["updated"];
        $count_error = $result["error"];
        if ($count_created) {
            $msg_created = CAppUI::tr(
                "CEtabExterne-import_created",
                [
                    "var1" => $count_created,
                ]
            );
            CAppUI::stepAjax($msg_created, UI_MSG_OK);
        }
        if ($count_updated) {
            $msg_updated = CAppUI::tr(
                "CEtabExterne-import_updated",
                [
                    "var1" => $count_updated,
                ]
            );
            CAppUI::stepAjax($msg_updated, UI_MSG_OK);
        }
        if ($count_error) {
            $msg_error = CAppUI::tr(
                "CEtabExterne-import_error",
                [
                    "var1" => $count_error,
                ]
            );
            CAppUI::stepAjax($msg_error, UI_MSG_ERROR);
        }
        CApp::rip();
    }
}
