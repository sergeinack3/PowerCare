<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Livi\Controllers\Legacy;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Livi\LiviExporter;

class LiviLegacyController extends CLegacyController
{
    /**
     *
     */
    public function exportPatientsLivi(): void
    {
        $this->checkPermEdit();

        $date_debut = CMbDT::date("last week");
        $date_fin   = CMbDT::date();
        $this->renderSmarty(
            "vw_export_patients_livi",
            [
                "date_debut" => $date_debut,
                "date_fin"   => $date_fin,
            ]
        );
    }

    /**
     * @throws CMbException
     * @throws Exception
     */
    public function importCsvPatientsLivi(): void
    {
        $this->checkPermEdit();

        $form_file = CValue::files('formfile');

        if (!array_key_exists('tmp_name', $form_file) || $form_file['tmp_name'][0] == '') {
            CAppUI::stepAjax('common-error-No file found.', UI_MSG_ERROR);

            return;
        }

        $date_debut = CView::post("date_debut", "str notNull");
        $date_fin   = CView::post("date_fin", "str notNull");

        CView::checkin();

        // Récupération des identifiants livi du fichier uploadé
        if (strtolower(pathinfo($form_file['name'][0], PATHINFO_EXTENSION)) !== 'csv') {
            throw new CMbException("not a csv");
        }

        ob_start();

        $csv      = new CCSVFile($form_file['tmp_name'][0], CCSVFile::PROFILE_OPENOFFICE);
        $exporter = LiviExporter::fromCsv($csv);
        $zip_name = $exporter->toZip($date_debut, $date_fin);

        ob_clean();

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($zip_name) . '"');
        header('Content-Length: ' . filesize($zip_name));

        readfile($zip_name);

        CApp::rip();
    }
}
