<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Controllers\Legacy\Import;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CView;
use Ox\Core\Import\CMbObjectExport;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CCSVImportPatients;
use Ox\Mediboard\Patients\CCSVImportSejours;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\Export\CXMLPatientExport;
use Ox\Mediboard\Patients\Import\Xml\PatientImportManager;
use Ox\Mediboard\PlanningOp\CChargePriceIndicator;
use Ox\Mediboard\PlanningOp\CModeEntreeSejour;
use Ox\Mediboard\PlanningOp\CModeSortieSejour;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Controller in charge of the importation of CPatient
 */
class PatientImportLegacyController extends CLegacyController
{
    /**
     * Display the CSV importation view.
     * Also check if the uploaded file is a valid import file.
     *
     * TODO : Should ref this to have a function to display the view and another one to check the file.
     *
     * @throws Exception
     */
    public function vw_import(): void
    {
        $this->checkPermAdmin();

        // Nombre de patients
        $patient = new CPatient();

        // import temp file
        $start_pat = 0;
        $count_pat = 20;
        if ($data = @file_get_contents(CAppUI::conf("root_dir") . "/tmp/import_patient.txt", "r")) {
            $nb        = explode(";", $data);
            $start_pat = $nb[0];
            $count_pat = $nb[1];
        }

        $patient_specs = [
            '_IPP'                  => $patient->_props['_IPP'],
            'identifiants_externes' => 'str',
        ];

        $patient_specs = array_merge($patient_specs, $patient->getPlainProps());

        $start_sej = 0;
        $count_sej = 20;
        if ($data = @file_get_contents(CAppUI::conf("root_dir") . "/tmp/import_cegi_sejour.txt", "r")) {
            $nb        = explode(";", $data);
            $start_sej = $nb[0];
            $count_sej = $nb[1];
        }

        $sejour    = new CSejour();
        $patient   = new CPatient();
        $mediusers = new CMediusers();

        $group_id = CGroups::loadCurrent()->_id;

        $mode_traitement           = new CChargePriceIndicator();
        $mode_traitement->group_id = $group_id;
        $mode_traitement->actif    = 1;

        /** @var CChargePriceIndicator[] $modes_traitement */
        $modes_traitement = $mode_traitement->loadMatchingList();
        $MDT              = CMbArray::pluck($modes_traitement, 'code');

        $mode_entree           = new CModeEntreeSejour();
        $mode_entree->group_id = $group_id;
        $mode_entree->actif    = 1;

        /** @var CModeEntreeSejour[] $modes_entree */
        $modes_entree = $mode_entree->loadMatchingList();
        $MDE          = CMbArray::pluck($modes_entree, 'code');

        $mode_sortie           = new CModeSortieSejour();
        $mode_sortie->group_id = $group_id;
        $mode_sortie->actif    = 1;

        /** @var CModeSortieSejour[] $modes_sortie */
        $modes_sortie = $mode_sortie->loadMatchingList();
        $MDS          = CMbArray::pluck($modes_entree, 'code');

        $sejour_specs = [
            '_IPP'  => $patient->_props['_IPP'],
            '_NDA'  => $sejour->_props['_NDA'],
            'adeli' => $mediusers->_props['adeli'],
            'rpps'  => $mediusers->_props['rpps'],
        ];

        if ($MDT) {
            $sejour_specs['MDT'] = 'enum list|' . implode('|', $MDT) . ' notNull';
        }

        if ($MDE) {
            $sejour_specs['MDE'] = 'enum list|' . implode('|', $MDE);
        }

        if ($MDS) {
            $sejour_specs['MDS'] = 'enum list|' . implode('|', $MDS);
        }

        $sejour_specs = array_merge($sejour_specs, $sejour->getPlainProps());

        $patient_options            = CCSVImportPatients::$options;
        $patient_interop            = CCSVImportPatients::$options_interop;
        $patient_found              = CCSVImportPatients::$options_found;
        $patient_identito_main      = CCSVImportPatients::$identito_main;
        $patient_identito_secondary = CCSVImportPatients::$identito_secondary;

        $fields_import_sejour = CCSVImportSejours::$options;

        $allowed_types = ["Chirurgien", "Anesthésiste", "Médecin", "Dentiste", "Infirmière", "Sage Femme"];
        $praticiens    = CMbObjectExport::getPraticiensFromGroup($allowed_types);

        $this->renderSmarty(
            'vw_import',
            [
                'group'                      => CGroups::loadCurrent(),
                'praticiens'                 => $praticiens,
                'praticien_id'               => [],
                'count_pat'                  => $count_pat,
                'start_pat'                  => $start_pat,
                'patient_specs'              => $patient_specs,
                'patient_options'            => $patient_options,
                'patient_interop'            => $patient_interop,
                'patient_identito_main'      => $patient_identito_main,
                'patient_identito_secondary' => $patient_identito_secondary,
                'patient_found'              => $patient_found,
                'start_sej'                  => $start_sej,
                'count_sej'                  => $count_sej,
                'sejour_specs'               => $sejour_specs,
                'fields_import_sejour'       => $fields_import_sejour,
            ]
        );
    }

    /**
     * Do the importation of a CSV to create CPatient objects.
     *
     * @throws Exception
     */
    public function do_import_patient_csv(): void
    {
        $this->checkPermAdmin();

        ini_set("auto_detect_line_endings", true);

        // Basic
        $start    = CView::post("start", "num default|0");
        $count    = CView::post("count", 'num default|100');
        $callback = CView::post("callback", "str");

        // Action when a patient is found
        $patient_found = CView::post("patient_found", "str default|0");

        // interop fields
        $by_IPP          = CView::post("by_IPP", "str default|0");
        $generate_IPP    = CView::post("generate_IPP", "str default|0");
        $diable_handlers = CView::post('disable_handlers', "str default|0");

        // advanced options
        $no_create     = CView::post("no_create", "str default|0");
        $fail_on_empty = CView::post("fail_on_empty", "str default|0");

        // Identito fields
        $identito_nom            = CView::post("identito_nom", "str default|0");
        $identito_prenom         = CView::post("identito_prenom", "str default|0");
        $identito_naissance      = CView::post("identito_naissance", "str default|0");
        $identito_sexe           = CView::post("identito_sexe", "str default|0");
        $identito_prenoms_autres = CView::post("identito_prenoms_autres", "str default|0");
        $identito_tel            = CView::post("identito_tel", "str default|0");
        $identito_matricule      = CView::post("identito_matricule", "str default|0");
        $secondary_operand       = CView::post("secondary_operand", "str default|or");

        CView::checkin();

        CApp::setTimeLimit(600);
        CApp::setMemoryLimit("1024M");

        if ($diable_handlers) {
            CApp::disableCacheAndHandlers();
        }

        CAppUI::stepAjax("Désactivation du gestionnaire", UI_MSG_OK);

        CMbObject::$useObjectCache = false;

        $import_patients = new CCSVImportPatients($start, $count);
        $import_patients->setOptions($by_IPP, $generate_IPP, $patient_found, $no_create, $fail_on_empty);
        $import_patients->setIdentito(
            $identito_nom,
            $identito_prenom,
            $identito_naissance,
            $identito_sexe,
            $identito_prenoms_autres,
            $identito_tel,
            $identito_matricule,
            $secondary_operand
        );

        $ret = $import_patients->import();

        $start += $count;
        file_put_contents(CAppUI::conf("root_dir") . "/tmp/import_patient.txt", "$start;$count");

        echo CAppUI::getMsg();

        if ($callback && $ret) {
            CAppUI::js("$callback($start,$count)");
        }

        CMbObject::$useObjectCache = true;
        CApp::rip();
    }

    /**
     * Do the importation of CPatient and other related objects using XML files.
     *
     * @throws Exception
     */
    public function do_import_patients_xml(): void
    {
        $this->checkPermAdmin();

        $directory       = CView::post("directory", "str notNull");
        $files_directory = CView::post(PatientImportManager::OPTION_FILES_DIRECTORY, "str");

        $directory       = rtrim(str_replace("\\\\", "\\", $directory), '/');
        $files_directory = str_replace("\\\\", "\\", $files_directory);

        if (!is_dir($directory)) {
            CAppUI::stepAjax('CPatientXMLImport-Error-Is not a directory', UI_MSG_ERROR, $directory);
        }

        if ($files_directory && !is_dir($files_directory)) {
            CAppUI::stepAjax('CPatientXMLImport-Error-Is not a directory', UI_MSG_ERROR, $files_directory);
        }

        $step  = CView::post("step", "num default|100");
        $start = CView::post("start", "num default|0");

        $options = $this->buildXmlOptions($files_directory);

        CView::setSession("step", $step);
        CView::setSession("start", $start);
        CView::setSession("directory", $directory);

        CView::checkin();

        CApp::setTimeLimit(600);
        CApp::setMemoryLimit("4096M");

        $manager    = new PatientImportManager($directory, (int)$start, (int)$step, $options);
        try {
            $count_dirs = $manager->import();
        } catch (CMbException $e) {
            CAppUI::stepAjax($e->getMessage(), UI_MSG_ERROR);
        }

        CAppUI::stepAjax('CPatientXMLImport-Info-Count patients found to import', UI_MSG_OK, $count_dirs);

        if ($count_dirs) {
            CAppUI::js("nextStepPatients()");
        }
    }

    /**
     * Tell if a directory contains importable directories.
     *
     * @throws Exception
     */
    public function ajax_check_import_dir(): void
    {
        $this->checkPermAdmin();

        $directory = CView::get("directory", 'str notNull');

        CView::checkin();

        if (!$directory) {
            return;
        }

        [
            'count_files'      => $count_files,
            'count_dirs'       => $count_dirs,
            'count_valid_dirs' => $count_valid_dirs,
        ] = CXMLPatientExport::checkDirectory($directory);

        CAppUI::stepAjax('CXMLPatientExport-Info-Directory contains %s files', UI_MSG_OK, $count_files);
        CAppUI::stepAjax(
            'CXMLPatientExport-Info-Directories with %s valide',
            UI_MSG_OK,
            $count_dirs,
            $count_valid_dirs
        );
    }

    /**
     * Build the options for the XML importation using the $_POST data.
     * Also put in session the options.
     *
     * @throws Exception
     */
    private function buildXmlOptions(?string $files_directory): array
    {
        $options = [
            PatientImportManager::OPTION_FILES_DIRECTORY           => $files_directory,
            PatientImportManager::OPTION_UPDATE_DATA               => (bool)CView::post('update_data', "str"),
            PatientImportManager::OPTION_PATIENT_ID                => CView::post('patient_id', "num"),
            PatientImportManager::OPTION_LINK_FILES_TO_OP          => (bool)CView::post('link_files_to_op', "str"),
            PatientImportManager::OPTION_CORRECT_FILES             => (bool)CView::post('correct_files', "str"),
            PatientImportManager::OPTION_HANDLERS                  => (bool)CView::post('handlers', "str"),
            PatientImportManager::OPTION_PATIENTS_ONLY             => (bool)CView::post('patients_only', "str"),
            PatientImportManager::OPTION_DATE_MIN                  => CView::post('date_min', "date"),
            PatientImportManager::OPTION_DATE_MAX                  => CView::post('date_max', "date"),
            PatientImportManager::OPTION_UF_REPLACE                => CView::post('uf_replace', "str"),
            PatientImportManager::OPTION_KEEP_SYNC                 => (bool)CView::post('keep_sync', "str"),
            PatientImportManager::OPTION_IGNORE_CLASSES            => CView::post('ignore_classes', "str"),
            PatientImportManager::OPTION_NO_UPDATE_PATIENTS_EXISTS => (bool)CView::post(
                'no_update_patients_exists',
                "str default|0"
            ),
            PatientImportManager::OPTION_IPP_TAG                   => CView::post('ipp_tag', "str"),
            PatientImportManager::OPTION_IMPORT_PRESC              => (bool)CView::post(
                'import_presc',
                "str default|0"
            ),
            PatientImportManager::OPTION_EXCLUDE_DUPLICATE         => (bool)CView::post(
                'exclude_duplicate',
                "str default|0"
            ),
        ];

        CView::setSession("files_directory", $files_directory);
        CView::setSession("update_data", $options[PatientImportManager::OPTION_UPDATE_DATA]);
        CView::setSession("patient_id", $options[PatientImportManager::OPTION_PATIENT_ID]);
        CView::setSession("link_files_to_op", $options[PatientImportManager::OPTION_LINK_FILES_TO_OP]);
        CView::setSession("correct_files", $options[PatientImportManager::OPTION_CORRECT_FILES]);
        CView::setSession("handlers", $options[PatientImportManager::OPTION_HANDLERS]);
        CView::setSession("patients_only", $options[PatientImportManager::OPTION_PATIENTS_ONLY]);
        CView::setSession("date_min", $options[PatientImportManager::OPTION_DATE_MIN]);
        CView::setSession("date_max", $options[PatientImportManager::OPTION_DATE_MAX]);

        return $options;
    }
}
