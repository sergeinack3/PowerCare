<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Controllers\Legacy;


use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CLegacyController;
use Ox\Core\CView;
use Ox\Interop\Eai\CInteropNorm;
use Ox\Interop\Eai\CMessageSupported;

class CMessageSupportedControllerLegacy extends CLegacyController
{
    public function messageRefreshSectionVersion(): void
    {
        $profile_class = CView::get("family", "str");

        /** @var CInteropNorm $profile */
        $profile = new $profile_class();

        $message_supported               = new CMessageSupported();
        $message_supported->object_id    = CView::get("object_id", "str");
        $message_supported->object_class = CView::get("object_class", "str");
        $message_supported->transaction  = CView::get("transaction", "str");
        $message_supported->profil       = CView::get("profil", "str");
        $message_supported->message      = CView::get("message", "str");
        CView::checkin();

        $this->renderSmarty(
            'inc_message_supported_section_version',
            [
                '_families'          => $profile,
                '_family_name'       => CClassMap::getInstance()->getShortName($profile),
                '_category_name'     => $message_supported->transaction,
                '_message_supported' => $message_supported,
            ]
        );
    }

    public function updateMessageSupported(): void
    {
        $transaction     = CView::get("transaction", "str");
        $old_transaction = CView::get("old_transaction", "str");
        $version         = CView::get("version", "str");

        $message_supported               = new CMessageSupported();
        $message_supported->object_id    = CView::get("object_id", "str");
        $message_supported->object_class = CView::get("object_class", "str");
        $message_supported->profil       = CView::get("profil", "str");
        $message_supported->transaction  = $old_transaction && $old_transaction !== $transaction ? $old_transaction : $transaction;
        $message_supported->active       = 1;
        CView::checkin();

        /** @var CMessageSupported[] $messages */
        $messages = $message_supported->loadMatchingList();
        foreach ($messages as $message) {
            $message->version     = $version;
            $message->transaction = $transaction;
            if ($msg = $message->store()) {
                CAppUI::stepAjax($msg, UI_MSG_ALERT);
            } else {
                CAppUI::stepAjax('CMessageSupported-msg-modify');
            }
        }
    }

    public function refreshMessageSupported(): void
    {
        $profile_class = CView::get("family", "str");

        /** @var CInteropNorm $profile */
        $profile = new $profile_class();
        $message_supported               = new CMessageSupported();
        $message_supported->object_id    = CView::get("object_id", "str");
        $message_supported->object_class = CView::get("object_class", "str");
        $message_supported->transaction  = CView::get("transaction", "str");
        $message_supported->message      = CView::get("message", "str");
        $message_supported->profil       = CView::get("profil", "str");
        $message_supported->version      = CView::get("version", "str");
        CView::checkin();

        $message_supported->loadMatchingObject();

        $this->renderSmarty(
            'inc_container_active_message_supported_form',
            [
                '_families'          => $profile,
                '_family_name'       => CClassMap::getInstance()->getShortName($profile),
                '_category_name'     => $message_supported->transaction,
                '_message_supported' => $message_supported,
            ]
        );
    }

}
