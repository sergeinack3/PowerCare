<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Controllers\Legacy;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CView;
use Ox\Mediboard\System\ViewSender\CViewSender;
use Ox\Mediboard\System\ViewSender\ViewSenderManager;

/**
 * Description
 */
class ViewSenderController extends CLegacyController
{
    public function vw_senders_monitoring_help(): void
    {
        $this->checkPermRead();

        $type = CView::get('type', 'enum list|source|sender notNull');

        CView::checkin();

        if ($type === 'sender') {
            $infos = [
                CAppUI::tr('CViewSender-last_status.triggered') => [
                    CAppUI::tr(
                        'CViewSender-last_status.triggered-desc'
                    ),
                    'warning',
                ],
                CAppUI::tr('CViewSender-last_status.producted') => [
                    CAppUI::tr(
                        'CViewSender-last_status.producted-desc'
                    ),
                    'ok',
                ],
            ];
        } else {
            $infos = [
                CAppUI::tr('CSourceToViewSender.last_status.triggered')
                => [CAppUI::tr('CSourceToViewSender.last_status.triggered-desc'), 'error'],
                CAppUI::tr('CSourceToViewSender.last_status.uploaded')
                => [CAppUI::tr('CSourceToViewSender.last_status.uploaded-desc'), 'ok'],
                CAppUI::tr('CSourceToViewSender.last_status.checked')
                => [CAppUI::tr('CSourceToViewSender.last_status.checked-desc'), 'ok'],
            ];
        }

        $this->renderSmarty('vw_senders_monitoring_help', ['infos' => $infos]);
    }

    /**
     * Execute all the CViewSender for the current minute. Each CViewSender is launch in async mode with curl.
     *
     * Cannot change the function name because it is used in tokens.
     */
    public function ajax_send_views(): void
    {
        $this->checkPermRead();

        $export = CView::get('export', 'bool default|1');

        CView::checkin();

        $manager = new ViewSenderManager($export);
        $senders = $manager->prepareAndSend();

        $this->renderSmarty(
            'inc_send_views',
            [
                'senders' => $senders,
                'time'    => $manager->getCurrentDateTime(),
                'minute'  => $manager->getMinute(),
            ]
        );
    }

    /**
     * Execute a single CViewSender and put the result in each exchange_source registered.
     */
    public function ajax_send_view(): void
    {
        $this->checkPermEdit();

        $view_sender_id = CView::get("view_sender_id", "ref class|CViewSender notNull");

        CView::checkin();

        CApp::registerShutdown(ViewSenderManager::SHUTDOWN_CLEAR_FUNCTION);

        $view_sender = new CViewSender();
        $view_sender->load($view_sender_id);

        if (!$view_sender->_id) {
            CAppUI::commonError("CViewSender.none");
        }

        if (!$view_sender->prepareAndSendFile()) {
            CAppUI::stepAjax("CViewSender-response-empty", UI_MSG_WARNING);
        } else {
            CAppUI::stepAjax("CViewSender-msg-sent", UI_MSG_OK);
        }
    }

    public function idx_view_senders(): void
    {
        $this->checkPermEdit();

        $this->renderSmarty('idx_view_senders');
    }

    public function ajax_form_view_sender(): void
    {
        $this->checkPermEdit();

        $sender_id = CView::getRefCheckEdit("sender_id", 'ref class|CViewSender');

        CView::checkin();

        $sender = CViewSender::findOrNew($sender_id);
        if ($sender->_id) {
            $sender->loadRefsNotes();
            $sender->loadRefSendersSource();
        }

        $this->renderSmarty('inc_form_view_sender', ['sender' => $sender]);
    }
}
