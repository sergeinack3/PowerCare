<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Controllers\Legacy\Export;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\Export\CCSVPatientExport;
use Ox\Mediboard\Patients\Export\CXMLPatientExport;
use ZipArchive;

/**
 * Controller in charge of the exportation of CPatient data
 */
class PatientExportLegacyController extends CLegacyController
{
    public function ajax_export_patients_csv(): void
    {
        $this->checkPermAdmin();

        $praticien_id = CView::get("praticien_id", "str");
        $date_min     = CView::get('date_min', 'str');
        $date_max     = CView::get('date_max', 'str');
        $patient_id   = CView::get('patient_id', 'ref class|CPatient');
        $all_prats    = CView::get('all_prats', 'str');

        CView::enforceSlave();
        CView::checkin();

        // Set system limits
        CApp::setTimeLimit(300);
        CApp::setMemoryLimit("1024M");

        $group = CGroups::loadCurrent();

        $export = new CCSVPatientExport($group, $praticien_id ?: []);
        if ($patient_id) {
            $export->exportPatient($patient_id);
        } else {
            $export->doExport((bool)$all_prats, $date_min, $date_max);
        }
    }

    public function vwExportPatients(): void
    {
        $this->checkPermAdmin();
        $step                 = CView::post("step", "num default|100", true);
        $start                = CView::post("start", "num default|0", true);
        $directory            = CView::post("directory", "str", true);
        $directory_name       = CView::post("directory_name", "str", true);
        $all_prats            = CView::post("all_prats", "str", true);
        $ignore_files         = CView::post("ignore_files", "str", true);
        $generate_pdfpreviews = CView::post("generate_pdfpreviews", "str", true);
        $date_min             = CView::post("date_min", "date", true);
        $date_max             = CView::post("date_max", "date", true);
        $patient_id           = CView::post("patient_id", "ref class|CPatient", true);
        $ignore_consult_tag   = CView::post("ignore_consult_tag", "str", true);
        $patient_infos        = CView::post("patient_infos", "str", true);
        $update               = CView::post("update", "str", true);
        $archive_type         = CView::get(
            "archive_type",
            "enum list|" . implode('|', CXMLPatientExport::ARCHIVE_TYPES)
            . ' default|' . CXMLPatientExport::ARCHIVE_TYPE_NONE,
            true
        );

        CView::checkin();

        $group = CGroups::loadCurrent();

        $this->renderSmarty(
            'vw_export_patients',
            [
                'group'                => $group,
                'functions'            => $group->loadFunctions(),
                'all_prats'            => $all_prats,
                'step'                 => $step,
                'start'                => $start,
                'directory'            => $directory,
                'directory_name'       => $directory_name,
                'ignore_files'         => $ignore_files,
                'generate_pdfpreviews' => $generate_pdfpreviews,
                'date_min'             => $date_min,
                'date_max'             => $date_max,
                'patient_id'           => $patient_id,
                'ignore_consult_tag'   => $ignore_consult_tag,
                'patient_infos'        => $patient_infos,
                'update'               => $update,
                'archive_type'         => $archive_type,
                'zip_available'        => class_exists(ZipArchive::class),
            ]
        );
    }

    public function do_export_patients(): void
    {
        $this->checkPermAdmin();

        $directory      = CView::post('directory', 'str notNull');
        $directory_name = CView::post('directory_name', 'str');

        $directory = stripslashes($directory);

        if (!is_dir($directory) || !is_writable($directory)) {
            CAppUI::stepAjax('CXMLPatientExport-Error-Directory must exists and be writable', UI_MSG_ERROR, $directory);
        }

        CView::setSession("directory", $directory);
        CView::setSession("directory_name", $directory_name);

        $options = $this->buildXmlOptions();

        if (!$options[CXMLPatientExport::OPTION_PRATICIENS]) {
            CAppUI::stepAjax('CXMLPatientExport-Error-Praticien is mandatory', UI_MSG_ERROR);
        }

        CApp::setTimeLimit(600);
        CApp::setMemoryLimit("4096M");

        CView::enforceSlave();

        CView::checkin();

        $directory_full = $directory . DIRECTORY_SEPARATOR . (($directory_name) ?: ("export-" . CMbDT::date()));

        $export        = new CXMLPatientExport($directory_full, $options);
        $patient_count = $export->export();

        CAppUI::stepAjax("%d patients à exporter", UI_MSG_OK, $export->getTotal());

        CAppUI::stepAjax("%d patients au total", UI_MSG_OK, $patient_count);

        if ($patient_count && !$options[CXMLPatientExport::OPTION_PATIENT]) {
            CAppUI::js("nextStepPatients()");
        }
    }

    /**
     * Build the options using the POST arguments.
     * This function must be called before CView::checkin because a session_write is done.
     *
     * @throws Exception
     */
    private function buildXmlOptions(): array
    {
        $options = [
            CXMLPatientExport::OPTION_STEP                   => CView::post(
                CXMLPatientExport::OPTION_STEP,
                'num default|10'
            ),
            CXMLPatientExport::OPTION_START                  => CView::post(
                CXMLPatientExport::OPTION_START,
                'num default|0'
            ),
            CXMLPatientExport::OPTION_PRATICIENS             =>
                explode(',', CView::post(CXMLPatientExport::OPTION_PRATICIENS, 'str')),
            CXMLPatientExport::OPTION_PATIENT                =>
                CView::post(CXMLPatientExport::OPTION_PATIENT, 'ref class|CPatient'),
            CXMLPatientExport::OPTION_DATE_MIN               => CView::post(CXMLPatientExport::OPTION_DATE_MIN, 'date'),
            CXMLPatientExport::OPTION_DATE_MAX               => CView::post(CXMLPatientExport::OPTION_DATE_MAX, 'date'),
            CXMLPatientExport::OPTION_IGNORE_CONST_WITH_TAGS =>
                (bool)CView::post(CXMLPatientExport::OPTION_IGNORE_CONST_WITH_TAGS, 'str'),
            CXMLPatientExport::OPTION_ARCHIVE_TYPE           => CView::post(
                CXMLPatientExport::OPTION_ARCHIVE_TYPE,
                'enum list|' . implode('|', CXMLPatientExport::ARCHIVE_TYPES)
                . ' default|' . CXMLPatientExport::ARCHIVE_TYPE_NONE
            ),
        ];

        CView::setSession(CXMLPatientExport::OPTION_PRATICIENS, $options[CXMLPatientExport::OPTION_PRATICIENS]);
        CView::setSession(CXMLPatientExport::OPTION_START, $options[CXMLPatientExport::OPTION_START]);
        CView::setSession(CXMLPatientExport::OPTION_STEP, $options[CXMLPatientExport::OPTION_STEP]);
        CView::setSession(CXMLPatientExport::OPTION_DATE_MIN, $options[CXMLPatientExport::OPTION_DATE_MIN]);
        CView::setSession(CXMLPatientExport::OPTION_DATE_MAX, $options[CXMLPatientExport::OPTION_DATE_MAX]);
        CView::setSession(CXMLPatientExport::OPTION_PATIENT, $options[CXMLPatientExport::OPTION_PATIENT]);
        CView::setSession(CXMLPatientExport::OPTION_ARCHIVE_TYPE, $options[CXMLPatientExport::OPTION_ARCHIVE_TYPE]);

        return $options;
    }
}
