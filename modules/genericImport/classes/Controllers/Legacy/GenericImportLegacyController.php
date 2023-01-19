<?php

/**
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport\Controllers\Legacy;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbPath;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Import\Framework\Entity\CImportCampaign;
use Ox\Import\GenericImport\AbstractOxPivotImportableObject;
use Ox\Import\GenericImport\CImportFile;
use Ox\Import\GenericImport\Exception\GenericImportException;
use Ox\Import\GenericImport\GenericImport;
use Ox\Import\GenericImport\ImportFilesManager;
use Ox\Import\GenericImport\OxImportPivot;

/**
 * Legacy controller for generic import
 */
class GenericImportLegacyController extends CLegacyController
{
    public const IMPORT_CSV = "csv";
    public const IMPORT_SQL = "sql";

    public function vw_import(): void
    {
        $this->checkPermEdit();
        $this->renderSmarty(
            'vw_import',
            [
                'import_type' => self::IMPORT_CSV,
            ]
        );
    }

    public function vw_import_sql(): void
    {
        $this->checkPermEdit();
        $this->renderSmarty(
            'vw_import_sql',
            [
                'import_type' => self::IMPORT_SQL,
            ]
        );
    }

    public function vw_importable_objects(): void
    {
        $this->checkPermRead();

        CView::checkin();

        $pivot = new OxImportPivot(false);

        $this->renderSmarty(
            'vw_importable_objects',
            [
                'importable_objects' => $pivot->getImportableClasses(false),
            ]
        );
    }

    public function vw_detail_import_class(): void
    {
        $this->checkPermRead();

        $class_name = $this->getClassNameFromGet(true);

        CView::checkin();

        if (!class_exists($class_name)) {
            throw GenericImportException::classIsNotImportable($class_name);
        }

        $pivot = new $class_name();

        if (!$pivot instanceof AbstractOxPivotImportableObject) {
            throw GenericImportException::classIsNotImportable($class_name);
        }

        $infos = $pivot->getImportableFields();

        $this->renderSmarty(
            'vw_detail_import_class',
            [
                'class_name' => $class_name,
                'infos'      => $infos,
                'add_infos'  => $pivot->getAdditionnalInfos(),
            ]
        );
    }

    public function ajax_download_file(): void
    {
        $this->checkPermRead();

        $class_name = $this->getClassNameFromGet();

        CView::checkin();

        $import_pivot = new OxImportPivot();
        $file         = ($class_name)
            ? $import_pivot->buildImportFile(new $class_name())
            : $import_pivot->buildMultipleImportFiles();

        $this->download($file);
        CApp::rip();
    }

    public function ajax_download_file_infos(): void
    {
        $this->checkPermRead();

        $class_name = $this->getClassNameFromGet();

        CView::checkin();

        if (!class_exists($class_name)) {
            throw GenericImportException::classIsNotImportable($class_name);
        }

        $import_pivot = new OxImportPivot();
        $file         = $import_pivot->buildImportFileInfos(new $class_name());

        $this->download($file);
        CApp::rip();
    }

    public function vw_upload_files(): void
    {
        $this->checkPermEdit();

        $current_campaign_id = CView::get('import_campaign_id', 'ref class|CImportCampaign', true);

        CView::checkin();

        $import_campaigns = CImportCampaign::getCampaignsInProgress();

        $this->renderSmarty(
            'vw_upload_files',
            ['campaigns' => $import_campaigns, 'current_campaign_id' => $current_campaign_id]
        );
    }

    public function vw_files_for_campaign(): void
    {
        $this->checkPermEdit();

        $import_campaign_id = CView::getRefCheckRead('import_campaign_id', 'ref class|CImportCampaign notNull');
        $update             = (bool)CView::get('update', 'bool default|0');

        CView::checkin();

        $campaign = CImportCampaign::findOrFail($import_campaign_id);

        $file_manager = new ImportFilesManager($campaign);
        $files        = $file_manager->listImportFiles();

        $this->renderSmarty(
            'vw_files_for_campaign',
            [
                'campaign'     => $campaign,
                'main_dir'     => $file_manager->getDirectory(),
                'files'        => $files,
                'update'       => $update,
                'entity_types' => GenericImport::AVAILABLE_TYPES,
            ]
        );
    }

    public function do_upload_files(): void
    {
        $this->checkPermEdit();

        $files              = CValue::files('formfile');
        $import_campaign_id = CView::postRefCheckRead('import_campaign_id', 'ref class|CImportCampaign notNull');
        $delete_files       = CView::post('delete_files', 'bool default|0');

        $campaign = CImportCampaign::findOrFail($import_campaign_id);

        CView::setSession('import_campaign_id', $campaign->_id);

        CView::checkin();

        $file_manager = new ImportFilesManager($campaign);
        $file_manager->uploadFiles($files, (bool)$delete_files);

        foreach ($file_manager->getUploadResults() as $msg) {
            CAppUI::setMsg(...$msg);
        }

        echo CAppUI::getMsg();
    }

    public function vw_files_mapping(): void
    {
        $this->checkPermEdit();

        $current_campaign_id = CView::get('import_campaign_id', 'ref class|CImportCampaign', true);

        CView::checkin();

        $import_campaigns = CImportCampaign::getCampaignsInProgress();

        $this->renderSmarty(
            'vw_files_mapping',
            ['campaigns' => $import_campaigns, 'current_campaign_id' => $current_campaign_id]
        );
    }

    public function do_link_import_file(): void
    {
        $this->checkPermEdit();

        $import_file_id = CView::post('import_file_id', 'ref class|CImportFile notNull');
        $type           = CView::post('type', 'enum list|' . implode('|', GenericImport::AVAILABLE_TYPES));

        CView::checkin();

        $import_file              = CImportFile::findOrFail($import_file_id);
        $import_file->entity_type = $type;

        if ($msg = $import_file->store()) {
            CAppUI::stepAjax($msg, UI_MSG_ERROR);
        }

        CAppUI::stepAjax('CImpotFile-msg-modify', UI_MSG_OK);

        CApp::rip();
    }

    private function download(string $file_path): void
    {
        $file_name    = CMbPath::getBasename($file_path);
        $content_type = (CMbPath::getExtension($file_name) === 'zip') ? 'application/zip' : 'text/plain';
        $content      = file_get_contents($file_path);

        ob_end_clean();

        header("Content-Type: {$content_type};charset=" . CApp::$encoding);
        header("Content-Disposition: attachment;filename=\"$file_name\"");
        header("Content-Length: " . strlen($content) . ";");

        echo $content;
    }

    private function getClassNameFromGet(bool $not_null = false): ?string
    {
        return str_replace('\\\\', '\\', CView::get('class_name', 'str' . (($not_null) ? ' notNull' : '')));
    }
}
