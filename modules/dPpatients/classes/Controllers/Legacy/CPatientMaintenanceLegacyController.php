<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Controllers\Legacy;

use DateTimeImmutable;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbObject;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CInseeImport;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CPatientSignature;
use Ox\Mediboard\Patients\Services\SourceIdentityFilesService;

/**
 * Description
 */
class CPatientMaintenanceLegacyController extends CLegacyController
{
    public function ajax_vw_table_duplicates(): void
    {
        $this->checkPermAdmin();

        $start = CView::get("start", "num default|0");
        $step  = CView::get("step", "num default|20");

        CView::checkin();

        CView::enforceSlave();

        $patient_signature = new CPatientSignature();
        $duplicates        = $patient_signature->findDuplicates((int)$start, (int)$step);

        $tpl_vars = [
            "duplicates"       => $duplicates,
            "start_duplicates" => $start,
            "step"             => $step,
        ];

        $this->renderSmarty('inc_identito_vigilance_tab_patients.tpl', $tpl_vars);
    }

    public function vw_identito_vigilance_pat(): void
    {
        $this->checkPermAdmin();

        CView::checkin();

        if (CAppUI::isCabinet() || CAppUI::isGroup()) {
            CAppUI::accessDenied();
        }

        $start = 0;

        $this->renderSmarty(
            'vw_identito_vigilance_pat.tpl',
            [
                'start_duplicates' => $start,
                'start_homonymes'  => $start,
            ]
        );
    }

    /**
     * Désactivation des correspondants médicaux sans date d'import
     * et avant une date sélectionée
     *
     * @throws \Exception
     */
    public function disableCorrespondentsWithoutImportDate(): void
    {
        $this->checkPermAdmin();

        $date = CView::get("date", "date default|" . CMbDT::date());

        CView::checkin();

        if ($date) {
            $date = CMbDT::date("-1 day", $date);
        }

        $count = (new CMedecin())->disableCorrespondentsWithoutImportDate($date);

        CApp::JSON($count);
    }

    public function do_import_insee(): void
    {
        $this->checkPermAdmin();

        $import = new CInseeImport();
        $import->importDatabase();

        foreach ($import->getMessages() as $message) {
            CAppUI::stepAjax(...$message);
        }

        CApp::rip();
    }

    public function listPatientsWithExpiredIdentityFiles(): void
    {
        $this->checkPermAdmin();

        $start = (int)CView::get('start', 'num default|0');
        $expiration_date = CView::get('expirationDate', 'date default|now');

        CView::checkin();

        $service = new SourceIdentityFilesService(new DateTimeImmutable($expiration_date));
        $patients = $service->getPatientsWithExpiredFiles($start);
        $sources = CMbObject::massLoadBackRefs($patients, 'sources_identite');
        CMbObject::massLoadBackRefs($sources, 'files');

        foreach ($patients as $patient) {
            $sources = $patient->loadRefsSourcesIdentite(false);
            foreach ($sources as $source) {
                $source->loadRefJustificatif();

                if (!$source->_ref_justificatif || !$source->_ref_justificatif->_id) {
                    unset($patient->_ref_sources_identite[$source->_id]);
                }
            }
        }

        $this->renderSmarty('maintenance/expired_identity_files', [
            'patients'       => $patients,
            'start'          => $start,
            'expirationDate' => $expiration_date === '' ? CMbDT::date() : $expiration_date,
            'total'          => $service->countPatientsWithExpiredFiles()
        ]);
    }

    public function deleteExpiredIdentityFiles(): void
    {
        $this->checkPermAdmin();

        $patients_guids = CView::post('patient_guids', ['str', 'default' => []]);

        CView::checkin();

        $patients = CMbObject::loadFromGuids($patients_guids);
        if (array_key_exists('CPatient', $patients)) {
            $results = (new SourceIdentityFilesService())->deleteExpiredIdentityFilesForPatients($patients['CPatient']);

            if (array_key_exists('success', $results) && $results['success']) {
                CAppUI::stepMessage(
                    UI_MSG_OK,
                    $results['success'] > 1 ? 'CSourceIdentite-msg-deleted_expired_identity_files'
                        : 'CSourceIdentite-msg-deleted_expired_identity_file',
                    $results['success']
                );
            }

            if (array_key_exists('errors', $results)) {
                foreach ($results['errors'] as $error) {
                    CAppUI::stepMessage(UI_MSG_ERROR, $error);
                }
            }
        } else {
            CAppUI::stepMessage(UI_MSG_WARNING, 'CPatient.none_selected');
        }
    }

    public function taskDeleteExpiredIdentityFiles(): void
    {
        $this->checkPermAdmin();

        $service = new SourceIdentityFilesService();
        $patients = $service->getPatientsWithExpiredFiles(0, 0);
        $results = $service->deleteExpiredIdentityFilesForPatients($patients);

        if ($results['success']) {
            CApp::log(CAppUI::tr('CSourceIdentite-msg-deleted_expired_identity_files', $results['success']));
        } else {
            CApp::log(CAppUI::tr('CSourceIdentite-msg-deleted_expired_identity_files.none'));
        }

        if ($results['errors'] && count($results['errors']) > 1) {
            CApp::log(CAppUI::tr('CSourceIdentite-error-deleted_expired_identity_files', count($results['errors'])));
            CApp::log(print_r($results['errors'], true));
        }
    }
}
