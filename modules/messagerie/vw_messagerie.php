<?php

/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Medimail\CMedimailAccount;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Messagerie\CUserMessageDest;
use Ox\Mediboard\MondialSante\CMondialSanteAccount;
use Ox\Mediboard\Mssante\CMSSanteUserAccount;
use Ox\Mediboard\System\CSourcePOP;

CCanDo::checkRead();

$selected_user_id   = CView::get('selected_user', 'ref class|CMediusers');
$contact_support_ox = CView::get('contact_support_ox', 'bool default|0');
$context            = CView::get('context', 'str');
$mail_subject       = CView::get('mail_subject', 'str');

CView::checkin();

$current_user = CMediusers::get();

$selected_user = new CMediusers();
$selected_user->load($selected_user_id);
if (!$selected_user->_id) {
    $selected_user = $current_user;
}

$accounts_user = [];
$users         = [];

$group = CGroups::loadCurrent();

$internal_mails_unread = 0;
if (CAppUI::gconf('messagerie access allow_internal_mail')) {
    $internal_mails_unread = CUserMessageDest::getUnreadMessages(true);
}

if (CAppUI::gconf('messagerie access allow_external_mail')) {
    /* Getting the list of the CSourcePop linked to the selected user */
    /** @var CSourcePOP[] $accounts_available */
    $accounts_available = CSourcePOP::getAccountsFor($selected_user, false);

    //getting user list
    foreach ($accounts_available as $_account) {
        $userPop              = $_account->loadRefMetaObject();
        $users[$userPop->_id] = $userPop;
    }

    $accounts_user = CSourcePOP::getAccountsFor($selected_user);

    foreach ($accounts_user as $account) {
        $account->getUnreadMessages(true);
    }
}

$apicrypt_account = false;
if (CModule::getActive('apicrypt') && CModule::getCanDo('apicrypt')->read) {
    $visible = false;
    if ($current_user->_id != $selected_user->_id) {
        $visible = true;
    }
    $apicrypt_account = CSourcePOP::getApicryptAccountFor($selected_user, $visible);

    if (!$apicrypt_account->_id) {
        $apicrypt_account = false;
    } else {
        $apicrypt_account->getUnreadMessages(true);
    }
}

$mssante_account = false;
if (CModule::getActive('mssante') && CModule::getCanDo('mssante')->read) {
    $mssante_account = CMSSanteUserAccount::getAccountFor($selected_user);
    if (!$mssante_account->_id) {
        $mssante_account = false;
    } else {
        $mssante_account->getUnreadMessages(true);
    }
}

$medimail_account                = false;
$medimail_account_application    = false;
$medimail_account_organisational = false;
if (CModule::getActive('medimail') && CModule::getCanDo('medimail')->read) {
    // Personal account
    $medimail_account = CMedimailAccount::getAccountFor($selected_user);
    if (!$medimail_account->_id) {
        $medimail_account = false;
    } else {
        $medimail_account->getUnreadMessages(true);
    }

    // Application account
    if (CAppUI::pref("allowed_access_application_mailbox")) {
        $medimail_account_application = CGroups::get()->loadRefMedimailAccount();
        if (!$medimail_account_application->_id) {
            $medimail_account_application = false;
        } else {
            $medimail_account_application->getUnreadMessages(true);
        }
    }

    // Organisational account
    if (CAppUI::pref("allowed_access_organisational_mailbox")) {
        $medimail_account_organisational = CFunctions::getCurrent()->loadRefMedimailAccount();
        if (!$medimail_account_organisational->_id) {
            $medimail_account_organisational = false;
        } else {
            $medimail_account_organisational->getUnreadMessages(true);
        }
    }
}

$mondial_sante_account = false;
if (CModule::getActive('mondialSante') && CModule::getCanDo('mondialSante')->read()) {
    $mondial_sante_account = CMondialSanteAccount::getAccountFor($selected_user);
    if (!$mondial_sante_account->_id) {
        $mondial_sante_account = false;
    } else {
        $mondial_sante_account->getUnreadMessages(true);
    }
}

$smarty = new CSmartyDP('modules/messagerie');
$smarty->assign('user', $current_user);
$smarty->assign('selected_user', $selected_user);
$smarty->assign('users_list', $users);
$smarty->assign('internal_mails_unread', $internal_mails_unread);
$smarty->assign('pop_accounts', $accounts_user);
$smarty->assign('apicrypt_account', $apicrypt_account);
$smarty->assign('mssante_account', $mssante_account);
$smarty->assign('medimail_account', $medimail_account);
$smarty->assign('medimail_account_application', $medimail_account_application);
$smarty->assign('medimail_account_organisational', $medimail_account_organisational);
$smarty->assign('mondial_sante_account', $mondial_sante_account);
$smarty->assign('contact_support_ox', $contact_support_ox);
$smarty->assign('context', $context);
$smarty->assign('mail_subject', $mail_subject);
$smarty->display('vw_messagerie.tpl');
