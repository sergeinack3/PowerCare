<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin\Controllers\Legacy;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbModelNotFoundException;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Core\Security\Csrf\AntiCsrf;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\Exception\CouldNotActivateAccount;
use Ox\Mediboard\Admin\Services\AccountActivationService;
use Ox\Mediboard\Admin\UsersPermissionsGrid;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Description
 */
class AdminLegacyController extends CLegacyController
{
    /**
     * @throws CMbModelNotFoundException
     * @throws CouldNotActivateAccount
     * @throws Exception
     */
    public function generateActivationToken(): void
    {
        $this->checkPermAdmin();

        $params = AntiCsrf::validatePOST();

        CView::checkin();

        $user_id = $params['user_id'];
        $type    = $params['type'];
        $email   = $params['email'] ?? null;

        $user = CUser::findOrFail($user_id);
        $user->needsEdit();

        switch ($type) {
            default:
            case 'token':
                $service = new AccountActivationService($user);
                $token   = $service->generateToken();

                CAppUI::callbackAjax(
                    'window.prompt',
                    CAppUI::tr('common-msg-Here is your account activation link :'),
                    $token->getUrl()
                );
                break;

            case 'email':
                try {
                    $service = new AccountActivationService($user, AccountActivationService::getSMTPSource());

                    if ($service->sendTokenViaEmail($email, new CSmartyDP('modules/admin'))) {
                        CAppUI::setMsg('AdminLegacyController-msg-Activation mail sent.', UI_MSG_OK);
                    } else {
                        CAppUI::setMsg('AdminLegacyController-msg-Unable to send activation mail.', UI_MSG_ERROR);
                    }
                } catch (CouldNotActivateAccount $e) {
                    CAppUI::setMsg($e->getMessage(), UI_MSG_ERROR);
                }
                break;
        }

        echo CAppUI::getMsg();

        CApp::rip();
    }

    /**-
     * Grille des droits utilisateurs et profils
     * @throws Exception
     */
    public function viewAllPerms(): void
    {
        $this->checkPermRead();

        $users_ids    = CView::get('users_ids', 'str');
        $profiles_ids = CView::get('profiles_ids', 'str');
        $only_profil  = CView::get('only_profil', 'bool default|0');
        $only_user    = CView::get('only_user', 'bool default|0');

        CView::checkin();
        CView::enforceSlave();

        if ($profiles_ids && $users_ids) {
            $only_user = $only_profil = 0;
        } elseif ($profiles_ids && !$users_ids) {
            $only_user = 1;
        } elseif (!$profiles_ids && $users_ids) {
            $only_profil = 1;
        }

        $profils = $profiles_ids ?: [];
        $users   = $users_ids ?: [];
        $group   = CGroups::loadCurrent();

        $list_modules = CModule::getActive();

        $user_permissions_grid = new UsersPermissionsGrid(
            $group,
            $only_profil,
            $only_user,
            $profils,
            $users,
            $list_modules
        );

        $this->renderSmarty(
            'vw_all_perms',
            [
                'list_modules'   => $list_modules,
                'list_functions' => $user_permissions_grid->getListFunctions(),
                'users_ids'      => $users_ids,
                'matrice'        => $user_permissions_grid->getMatrix(),
                'profiles_ids'   => $profiles_ids,
                'profiles'       => $user_permissions_grid->getProfiles(),
                'matrix_profil'  => $user_permissions_grid->getMatrixProfiles(),
                'only_profil'    => $only_profil,
                'only_user'      => $only_user,
            ]
        );
    }
}
