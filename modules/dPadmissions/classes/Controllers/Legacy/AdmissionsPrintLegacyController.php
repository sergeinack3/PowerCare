<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admissions\Controllers\Legacy;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CLegacyController;
use Ox\Core\CMbArray;
use Ox\Core\CMbPDFMerger;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CHtmlToPDF;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Impression dans les admissions
 */
class AdmissionsPrintLegacyController extends CLegacyController
{
    public function printFichesAnesth(): void
    {
        CApp::setMemoryLimit("2G");
        CApp::setTimeLimit(300);

        CCanDo::checkRead();

        $sejours_ids = CView::post("sejours_ids", "str");

        CView::checkin();

        // Chargement des séjours
        $sejour = new CSejour();

        $where              = [];
        $where["sejour_id"] = "IN ($sejours_ids)";

        /** @var CSejour[] $sejours */
        $sejours = $sejour->loadList($where);

        CStoredObject::massLoadFwdRef($sejours, "patient_id");

        foreach ($sejours as $_sejour) {
            $_sejour->loadRefPatient();
        }

        // Tri par nom de patient
        $sorter_nom    = CMbArray::pluck($sejours, "_ref_patient", "nom");
        $sorter_prenom = CMbArray::pluck($sejours, "_ref_patient", "prenom");
        array_multisort($sorter_nom, SORT_ASC, $sorter_prenom, SORT_ASC, $sejours);

        $pdf_merger = new CMbPDFMerger();
        $htmltopdf  = new CHtmlToPDF();
        $files = [];

        $style = file_get_contents('./style/mediboard_ext/standard.css');
        $style = preg_replace('#\/\*.*(\*)*\*\/#msU', '', $style);

        $consult_anesths = [];

        foreach ($sejours as $_sejour) {
            $_operation = $_sejour->loadRefLastOperation();

            if (!$_operation->_id) {
                continue;
            }

            $consult_anesth = $_operation->loadRefsConsultAnesth();

            if ($consult_anesth->_id) {
                $consult_anesths[$consult_anesth->_id] = $consult_anesth;
            }
        }

        if (!count($consult_anesths)) {
            CAppUI::stepAjax('CConsultAnesth.none', UI_MSG_WARNING);
            return;
        }

        foreach ($consult_anesths as $consult_anesth) {
            $result = CApp::fetch(
                "dPcabinet",
                "print_fiche",
                [
                    "dossier_anesth_id" => $consult_anesth->_id,
                    "offline"           => 1,
                    "multi"             => 1,
                    'display'           => 1,
                ]
            );

            if (strpos($result, '%PDF') === false) {
                $result = "
                  <html>
                    <head>
                      <style type='text/css'>
                        {$style}
                      </style>
                    </head>
                    <body>
                      {$result}
                    </body>
                  </html>";

                // Remplace du src de l'image de la condition d'intubation
                $root_dir = CAppUI::conf('root_dir');
                $result = preg_replace('#<img src="#', '<img src="' . $root_dir . '/', $result);

                $result = $htmltopdf->generatePDF($result, 0, new CCompteRendu(), new CFile(), true, false);
            }

            $temp_file = tempnam('./tmp', 'fiche');
            file_put_contents($temp_file, $result);

            $pdf_merger->addPDF($temp_file);

            $files[] = $temp_file;
        }

        $pdf_merger->merge();

        foreach ($files as $_file) {
            unlink($_file);
        }
    }
}
