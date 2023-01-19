<?php

/**
 * @package Mediboard\Import
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Controllers\Legacy;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CView;
use Ox\Core\Logger\LoggerLevels;
use Ox\Import\Framework\CFwImport;
use Ox\Import\Framework\Entity\CImportCampaign;
use Ox\Import\Framework\Entity\Manager;
use Ox\Import\Framework\Entity\User;
use Ox\Import\Framework\Exception\ImportException;
use Ox\Mediboard\Mediusers\CMediusers;
use Throwable;

abstract class CImportFwLegacyController extends CLegacyController
{
    public function do_bind_user()
    {
        $this->checkPermEdit();

        $campaign_id = CView::post('campaign_id', 'ref class|CImportCampaign notNull');
        $ext_id      = CView::post('ext_id', 'str notNull');
        $user_id     = CView::postRefCheckRead('user_id', 'ref class|CMediusers notNull');

        CView::checkin();

        $import_campaign = CImportCampaign::findOrFail($campaign_id);
        if (!$import_campaign->getPerm(PERM_READ) || $import_campaign->closing_date) {
            CAppUI::accessDenied();
        }

        $mediuser = CMediusers::findOrFail($user_id);

        $user = new User();
        $user->setExternalId($ext_id);

        try {
            $import_campaign->addImportedObject($user, $mediuser);
        } catch (ImportException $e) {
            CAppUI::stepAjax($e->getMessage());
            CApp::rip();
        }

        CAppUI::stepAjax('CImportEntity-msg-create');
        CApp::rip();
    }

    public function vw_users_fw()
    {
        $this->checkPermEdit();

        $last_import_campaign_id = CView::get('import_campaign_id', 'ref class|CImportCampaign', true);
        $import_type             = CView::get('import_type', 'str');

        CView::checkin();

        $campaigns = CImportCampaign::getCampaignsInProgress();

        $this->renderSmarty(
            'vw_users',
            [
                'last_campaign_id' => $last_import_campaign_id,
                'campaigns'        => $campaigns,
                'module'           => $this->getModName(),
                'a_value'          => 'ajax_list_users_fw',
                'import_type'      => $import_type,
            ],
            'modules/import'
        );
    }

    abstract protected function getModName(): string;

    public function ajax_list_users_fw(): void
    {
        $this->checkPermEdit();

        $campaign_id = CView::get('import_campaign_id', 'ref class|CImportCampaign notNull', true);
        $start       = CView::get('start', 'num default|0');
        $step        = CView::get('step', 'num default|50');
        $import_type = CView::get('import_type', 'str');

        $campaign = CImportCampaign::findOrFail($campaign_id);
        if (!$campaign->getPerm(PERM_EDIT) || $campaign->closing_date) {
            CAppUI::accessDenied();
        }

        CView::setSession('import_campaign_id', $campaign_id);

        CView::checkin();

        $import = $this->getImportInstance($import_type);
        $import->setCampaign($campaign);

        $user_list = $import->listUsers($campaign, $start, $step);
        $total     = $this->countUsers($import);

        $this->renderSmarty(
            'inc_list_users',
            [
                'campaign'        => $campaign,
                'user_list'       => $user_list,
                'total'           => $total,
                'start'           => $start,
                'mod_name'        => $this->getModName(),
                'change_page_arg' => [
                    'm'                  => $this->getModName(),
                    'a'                  => 'ajax_list_users_fw',
                    'import_campaign_id' => $campaign->_id,
                    'refresh'            => 'result-list-users',
                    'import_type'        => $import_type,
                ],
            ],
            'modules/import'
        );
    }

    abstract protected function getImportInstance(?string $type = null): CFwImport;

    protected function countUsers(CFwImport $import): int
    {
        return $import->count($this->getUsersTable());
    }

    protected function getUsersTable(): string
    {
        return 'users';
    }

    /**
     * @throws Exception
     */
    public function vw_import_fw(): void
    {
        $this->checkPermEdit();

        $last_campaign_import_id = CView::get('import_campaign_id', 'ref class|CImportCampaign', true);
        $import_type             = CView::get('import_type', 'str');

        CView::checkin();

        $campaigns = CImportCampaign::getCampaignsInProgress();
        $import    = $this->getImportInstance($import_type);

        if ($last_campaign_import_id) {
            $campaign = CImportCampaign::find($last_campaign_import_id);
        } else {
            $campaign                = CImportCampaign::getLastCampaign();
            $last_campaign_import_id = $campaign->_id;
        }

        $import->setCampaign($campaign);
        $import_order = $import->getImportOrder();

        $this->renderSmarty(
            'vw_import',
            [
                'module'           => $this->getModName(),
                'a_value'          => 'ajax_vw_import_fw',
                'last_campaign_id' => $last_campaign_import_id,
                'campaigns'        => $campaigns,
                'import_order'     => $import_order,
                'import_type'      => $import_type,
            ],
            'modules/import'
        );
    }

    public function ajax_vw_import_fw(): void
    {
        $this->checkPermEdit();

        $type        = CView::get('type', 'str notNull');
        $campaign_id = CView::get('import_campaign_id', 'ref class|CImportCampaign notNull', true);
        $import_type = CView::get('import_type', 'str');

        $campaign = CImportCampaign::findOrFail($campaign_id);
        if (!$campaign->getPerm(PERM_EDIT) || $campaign->closing_date) {
            CAppUI::accessDenied();
        }

        CView::setSession('import_campaign_id', $campaign_id);

        CView::checkin();

        $import = $this->getImportInstance($import_type);
        $import->setCampaign($campaign);

        try {
            $total = $this->count($import, $type);
        } catch (Throwable $e) {
            CApp::log($e->getMessage(), null, LoggerLevels::LEVEL_ERROR);

            CAppUI::stepAjax(
                'CImportFwLegacyController-Error-Mapper does not exist or cannot be instanciated for type',
                UI_MSG_ERROR,
                $type
            );
        }

        $last_id = $import->getLastId($type);

        $this->renderSmarty(
            'inc_vw_import',
            [
                'type'        => $type,
                'total'       => $total,
                'last_id'     => ($last_id) ?: 0,
                'module'      => $this->getModName(),
                'by_patient'  => $this->isImportByPatient($type),
                'import_type' => $import_type,
            ],
            'modules/import'
        );
    }

    protected function count(CFwImport $import, string $type, ?string $patient_id = null): int
    {
        return $import->count($type, $patient_id);
    }

    protected function isImportBypatient(string $type): bool
    {
        return true;
    }

    public function do_import_fw(): void
    {
        $this->checkPermEdit();

        $campaign_id = CView::post('import_campaign_id', 'ref class|CImportCampaign notNull');
        $type        = CView::post('type', 'str');
        $start       = CView::post('start', 'num default|0');
        $step        = CView::post('step', 'num default|100');
        $patient_id  = CView::post('patient_id', 'str');
        $update      = CView::post('update', 'bool default|0');
        $import_type = CView::post('import_type', 'str');

        $campaign = CImportCampaign::findOrFail($campaign_id);
        if (!$campaign->getPerm(PERM_EDIT) || $campaign->closing_date) {
            CAppUI::accessDenied();
        }

        CView::setSession('import_campaign_id', $campaign_id);

        CView::checkin();

        $import = $this->getImportInstance($import_type);
        $import->setCampaign($campaign);

        if ($patient_id) {
            $patient_id = str_replace(' ', '', $patient_id);
            $patient_id = explode(',', $patient_id);

            foreach ($patient_id as $id) {
                $manager = $import->import($campaign, $type, $start, $step, $id, (bool)$update);
            }
        } else {
            $manager = $import->import($campaign, $type, $start, $step, null, (bool)$update);
        }

        if (!$manager instanceof Manager) {
            CAppUI::stepAjax($manager, UI_MSG_ERROR);
        }

        $start = $start + $step;

        foreach ($manager->getMessages() as $_msg) {
            CAppUI::setMsg(...$_msg);
        }

        foreach ($manager->getErrors() as $_error) {
            CAppUI::setMsg($_error, UI_MSG_WARNING);
        }

        $ended = ($import->getImportCount() < $step);
        if ($ended) {
            CAppUI::setMsg('common-msg-Importation done.');
        }

        echo CAppUI::getMsg();

        CAppUI::js("ImportMapping.nextImport('{$type}', '{$start}', '{$ended}');");

        CApp::rip();
    }

    public function ajax_count_with_condition(): void
    {
        $this->checkPermEdit();

        $type        = CView::get('type', 'str notNull');
        $campaign_id = CView::get('import_campaign_id', 'ref class|CImportCampaign notNull', true);
        $patient_id  = CView::get('patient_id', 'str');
        $import_type = CView::get('import_type', 'str');

        CView::checkin();

        $campaign = CImportCampaign::findOrFail($campaign_id);
        if (!$campaign->getPerm(PERM_EDIT) || $campaign->closing_date) {
            CAppUI::accessDenied();
        }

        $import = $this->getImportInstance($import_type);
        $import->setCampaign($campaign);
        $total = $this->count($import, $type, $patient_id);
        echo $total;
    }
}
