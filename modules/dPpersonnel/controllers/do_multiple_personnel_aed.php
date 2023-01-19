<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CPermObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Personnel\CPersonnel;

CCanDo::checkAdmin();

$user_ids = CView::post('user_id', 'str notNull');
$types    = CView::post('emplacement', 'str notNull');
$actif    = CView::post('actif', 'bool default|1');

CView::checkin();

$group    = CGroups::loadCurrent();
$created  = 0;
$modified = 0;
$errors   = 0;

foreach ($user_ids as $user_id) {
    // Permissions sur les établissements de l'utilisateur courant
    CPermObject::loadUserPerms($user_id);
    $can_edit_etab = CPermObject::getPermObject($group, PERM_EDIT, null, $user_id);
    $user = CMediusers::find($user_id);
    if ($can_edit_etab) {
        foreach ($types as $type) {
            $personnel              = new CPersonnel();
            $personnel->user_id     = $user_id;
            $personnel->emplacement = $type;
            $personnel->loadMatchingObjectEsc();

            $personnel->actif = $actif;

            if ($msg = $personnel->store()) {
                $errors++;
            } elseif ((bool)$personnel->_id) {
                $created++;
            } else {
                $modified++;
            }
        }
    } else {
        CAppUI::setMsg(
            CAppUI::tr(
                "CPersonnel-msg-This person does not have the rights to be created in this establishment multiple",
                $user->_view
            ),
            UI_MSG_ERROR
        );
    }
}

if ($created) {
    CAppUI::setMsg(CAppUI::tr('CPersonnel-msg-create-multiple', $created), UI_MSG_OK);
}
if ($modified) {
    CAppUI::setMsg(CAppUI::tr('CPersonnel-msg-modify-multiple', $modified), UI_MSG_OK);
}
if ($errors) {
    CAppUI::setMsg(CAppUI::tr('CPersonnel-msg-errors-multiple', $errors), UI_MSG_ERROR);
}

echo CAppUI::getMsg();
