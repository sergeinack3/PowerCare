<?php

/**
 * @package Mediboard\Rpps
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Rpps\Controllers\Legacy;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Core\Logger\LoggerLevels;
use Ox\Import\Rpps\CExternalMedecinBulkImport;
use Ox\Import\Rpps\CExternalMedecinSync;
use Ox\Import\Rpps\CMedecinExercicePlaceManager;
use Ox\Import\Rpps\CRppsFileDownloader;
use Ox\Import\Rpps\Entity\CDiplomeAutorisationExercice;
use Ox\Import\Rpps\Entity\CMssanteInfos;
use Ox\Import\Rpps\Entity\CPersonneExercice;
use Ox\Import\Rpps\Entity\CSavoirFaire;
use Ox\Import\Rpps\Exception\CImportMedecinException;
use Ox\Mediboard\Patients\CMedecin;

/**
 * Description
 */
class CRppsLegacyController extends CLegacyController
{
    public function ajax_create_schema(): void
    {
        $this->checkPermAdmin();

        CView::checkin();

        $import = new CExternalMedecinBulkImport();

        if (!$import->createSchema()) {
            CAppUI::commonError();
        }

        CAppUI::stepAjax('CExternalMedecinBulkImport-msg-Tables created');

        CApp::rip();
    }

    public function ajax_populate_database(): void
    {
        $this->checkPermAdmin();

        CView::checkin();

        CApp::setTimeLimit(300);

        $downloader = new CRppsFileDownloader();

        // Download CPersonExercice file
        if ($downloader->downloadRppsFile(CRppsFileDownloader::DOWNLOAD_RPPS_FILE_URL)) {
            CAppUI::stepAjax('CRppsFileDownloader-msg-Info-RPPS file downloaded and extracted');
        }

        // Doawnload MSSante file
        if ($downloader->downloadRppsFile(CRppsFileDownloader::DOWNLOAD_MSSANTE_URL)) {
            CAppUI::stepAjax('CRppsFileDownloader-msg-Info-MSSante file downloaded and extracted');
        }

        // Bulk import files in Database
        $import   = new CExternalMedecinBulkImport();
        $messages = $import->bulkImport();

        foreach ($messages as $_msg) {
            CAppUI::stepAjax(array_shift($_msg), UI_MSG_OK, ...$_msg);
        }

        CApp::rip();
    }

    public function configure(): void
    {
        $this->checkPermAdmin();

        $bulk_import    = new CExternalMedecinBulkImport();
        $can_load_local = $bulk_import->canLoadLocalInFile();

        $file_downloader = new CRppsFileDownloader();
        $is_downloadable = $file_downloader->isRppsFileDownloadable();

        $this->renderSmarty(
            'configure',
            [
                'can_load_local'  => $can_load_local,
                'is_downloadable' => $is_downloadable,
            ]
        );
    }

    /**
     * @throws CImportMedecinException
     * @throws Exception
     */
    public function cron_synchronize_medecin(): void
    {
        $this->checkPermEdit();

        $type        = CView::get('type', 'enum list|' . implode('|', CExternalMedecinSync::ALLOWED_TYPES));
        $input_codes = CView::get('codes', 'str');
        $step        = CView::get('step', 'num');

        if (empty($step)) {
            $step = intval(CAppUI::conf('rpps sync_step'));
        }

        CView::checkin();

        if (!$this->checkSyncEnabled()) {
            CApp::rip();
        }

        CAppUI::$localize = false;

        $codes = [];
        if ($input_codes) {
            $codes = array_map('trim', explode(',', $input_codes));
        }

        $sync = new CExternalMedecinSync();
        $sync->synchronizeSomeMedecins($step, $type, $codes);

        CAppUI::$localize = true;

        if ($errors = $sync->getErrors()) {
            CApp::log(CExternalMedecinSync::class . '::Errors : ' . count($errors), $errors, LoggerLevels::LEVEL_WARNING);
        }

        if ($updated = $sync->getUpdated()) {
            CApp::log(CExternalMedecinSync::class . '::Updated : ' . count($updated));
        }

        $stop = '1';
        if (count($errors) === 0 && count($updated) === 0) {
            $stop = '0';
        }

        CAppUI::stepAjax("Errors : " . (count($errors) . "\nUpdated : " . count($updated)));

        CAppUI::js("nextStep($stop)");
        CApp::rip();
    }

    public function cron_disable_exercice_places(): void
    {
        $this->checkPermEdit();

        $step = CView::get('step', 'num default|100');

        CView::checkin();

        if (!$this->checkSyncEnabled()) {
            CApp::rip();
        }

        $manager = new CMedecinExercicePlaceManager();
        $manager->removeOldMedecinExercicePlaces($step);
        // Ne pas désactiver les medecins sans lien vers des lieux d'exercice.
        // Ceci pourra être réactivé plus tard si besoin.
        //$manager->disableMedecinsWithoutExercicePlace($step);

        foreach ($manager->getInfos() as $_info) {
            CApp::log($_info);
        }

        foreach ($manager->getErrors() as $_err) {
            CApp::log($_err, null, LoggerLevels::LEVEL_WARNING);
        }

        CApp::rip();
    }

    public function vw_rpps(): void
    {
        $this->checkPermAdmin();

        $this->renderSmarty('vw_rpps');
    }

    public function vw_sync_external(): void
    {
        $this->checkPermRead();

        CView::checkin();

        $sync       = new CExternalMedecinSync();
        $avancement = $sync->getAvancement();

        $this->renderSmarty(
            'vw_sync_external',
            [
                'avancements' => [
                    'CPersonneExercice'            => $avancement[CPersonneExercice::class],
                    'CSavoirFaire'                 => $avancement[CSavoirFaire::class],
                    'CDiplomeAutorisationExercice' => $avancement[CDiplomeAutorisationExercice::class],
                    'CMssanteInfos'                => $avancement[CMssanteInfos::class],
                ],
            ]
        );
    }

    public function vw_sync_medecin(): void
    {
        $this->checkPermRead();

        CView::checkin();

        $medecin = new CMedecin();
        [$versions, $total] = $medecin->getSyncAvancement();

        $this->renderSmarty(
            'vw_sync_medecin',
            [
                'versions' => $versions,
                'total'    => $total,
            ]
        );
    }

    public function vw_synchronisation_state(): void
    {
        $this->checkPermRead();

        $this->renderSmarty('vw_synchronisation_state');
    }

    private function checkSyncEnabled(): bool
    {
        $ds = CSQLDataSource::get('rpps_import', true);
        if (!$ds || !$ds->hasTable('personne_exercice')) {
            return false;
        }

        return true;
    }
}
