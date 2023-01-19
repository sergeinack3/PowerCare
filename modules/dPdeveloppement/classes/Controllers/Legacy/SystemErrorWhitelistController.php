<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement\Controllers\Legacy;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\System\CErrorLog;
use Ox\Mediboard\System\CErrorLogWhiteList;
use Ox\Mediboard\System\Elastic\ErrorLogRepository;

class SystemErrorWhitelistController extends CLegacyController
{
    public function ajax_list_error_log_whitelist(): void
    {
        $this->checkPermRead();

        $error_log_whitelist = new CErrorLogWhiteList();
        $list                = $error_log_whitelist->loadList();
        CStoredObject::massLoadFwdRef($list, 'user_id');

        $this->renderSmarty(
            "inc_list_error_log_whitelist",
            [
                "list" => $list,
            ]
        );
    }

    public function ajax_toogle_error_log_whitelist(): void
    {
        $this->checkPermAdmin();

        $id             = CView::get('error_log_id', 'str');
        $is_elastic_log = CView::get('is_elastic_log', 'str');
        CView::checkin();

        if ($is_elastic_log === "true") {
            $error     = (new ErrorLogRepository())->findById($id);
            $error_log = $error->toCErrorLog();
        } else {
            $error_log = new CErrorLog();
            $error_log->load($id);
        }

        $error_log_whitelist       = new CErrorLogWhiteList();
        $error_log_whitelist->hash = $error_log->signature_hash;
        $error_log_whitelist->loadMatchingObject();

        if ($error_log_whitelist->_id) {
            $error_log_whitelist->delete();
        } else {
            $error_log_whitelist->text        = $error_log->text;
            $error_log_whitelist->type        = $error_log->error_type;
            $error_log_whitelist->file_name   = $error_log->file_name;
            $error_log_whitelist->line_number = $error_log->line_number;
            $error_log_whitelist->user_id     = CAppUI::$user->user_id;
            $error_log_whitelist->datetime    = CMbDT::dateTime();
            $error_log_whitelist->count       = 0;

            $msg = $error_log_whitelist->store();

            if ($error_log_whitelist->_id) {
                CAppUI::displayAjaxMsg('CErrorLog.whitelist_added', UI_MSG_OK);
            }
        }

        CApp::rip();
    }

    public function ajax_delete_error_log_whitelist(): void
    {
        $this->checkPermAdmin();

        $id  = CView::get("id", "num");
        $all = CView::get("all", "bool");
        CView::checkin();

        $wl = new CErrorLogWhiteList();

        if ($all) {
            $ds    = $wl->getDS();
            $query = "TRUNCATE {$wl->_spec->table}";
            $ds->exec($query);
            CAppUI::displayAjaxMsg('CErrorLogWhiteList-msg-emptied');
            CAppUI::callbackAjax('Control.Modal.close');
        } else {
            if (!$id) {
                trigger_error('CErrorLogWhiteList-error-missing id');
            }
            $wl->error_log_whitelist_id = $id;
            $wl->loadMatchingObject();
            $wl->delete();
            CAppUI::displayAjaxMsg('CErrorLogWhiteList-msg-deleted');
            CAppUI::callbackAjax('Control.Modal.refresh');
        }

        CApp::rip();
    }
}
