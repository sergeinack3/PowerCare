<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin\Controllers;

use Exception;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Request\RequestFilter;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CAppUI;
use Ox\Core\CController;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbModelNotFoundException;
use Ox\Core\CMbSecurity;
use Ox\Core\CSQLDataSource;
use Ox\Core\Kernel\Exception\HttpException;
use Ox\Mediboard\Admin\CLDAP;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\CViewAccessToken;
use Ox\Mediboard\Admin\Exception\CouldNotChangePassword;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Description
 */
class PermissionController extends CController
{
    /**
     * @deprecated
     * @param Request $request
     *
     * @return Response
     * @throws CMbModelNotFoundException
     */
    public function viewChangePassword(Request $request): Response
    {
        $force_change  = $request->get('force_change', false);
        $life_duration = $request->get('life_duration', false);

        $user = CUser::findOrFail(CAppUI::$user->_id);

        $user->updateSpecs();
        $user->isLDAPLinked();

        $password_spec = CAppUI::$user->_specs['_user_password'];
        $description   = $password_spec->getLitteralDescription();
        $description   = str_replace("'_user_username'", $user->user_username, $description);
        $description   = explode('. ', $description);
        array_shift($description);
        $description = array_filter($description);

        $url = $this->generateUrl('admin_change_password');

        return $this->renderSmartyResponse(
            'change_password',
            [
                'url'          => $url,
                'pw_spec'      => $password_spec,
                'user'         => $user,
                'forceChange'  => $force_change,
                'lifeDuration' => $life_duration,
                'lifetime'     => $user->conf("password_life_duration"),
                'description'  => $description,
            ]
        );
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws CouldNotChangePassword
     */
    public function changePassword(Request $request): Response
    {
        $old_pwd  = $request->request->get('old_pwd');
        $new_pwd1 = $request->request->get('new_pwd1');
        $new_pwd2 = $request->request->get('new_pwd2');
        $callback = $request->request->get('callback');

        $user     = CUser::get();
        $username = $user->user_username;

        if (!$user->checkActivationToken()) {
            // Vérification du mot de passe actuel de l'utilisateur courant
            $user = CUser::checkPassword($username, $old_pwd, true);
        }

        // Mot de passe actuel correct
        if (!$user->_id) {
            throw CouldNotChangePassword::passwordMismatch();
            //CAppUI::stepAjax("CUser-user_password-nomatch", UI_MSG_ERROR);
        }

        if (!$user->canChangePassword()) {
            throw CouldNotChangePassword::changingForbidden();
            //CAppUI::stepAjax("CUser-password_change_forbidden", UI_MSG_ERROR);
        }

        // Mots de passe différents
        if ($new_pwd1 != $new_pwd2) {
            throw CouldNotChangePassword::newPasswordsMismatch();
            //CAppUI::stepAjax("CUser-user_password-nomatch", UI_MSG_ERROR);
        }

        // Enregistrement
        $user->_user_password = $new_pwd1;
        $user->_is_changing   = true;

        // If user was obliged to change and successfully changed, remove flag
        if ($user->force_change_password) {
            $user->force_change_password = '0';
        }

        if ($msg = $user->store()) {
            dump($msg);
            CAppUI::stepAjax($msg, UI_MSG_ERROR);
        }

        // Si utilisateur associé au LDAP et modif mdp autorisée
        if ($user->isLDAPLinked()) {
            try {
                if (CLDAP::changePassword($user, $old_pwd, $new_pwd1)) {
                    CAppUI::resetPasswordRemainingDays();
                    CUser::resetPasswordMustChange();

                    CAppUI::stepAjax("CLDAP-change_password_succeeded", UI_MSG_OK);
                } else {
                    CAppUI::stepAjax("CLDAP-change_password_failed", UI_MSG_WARNING);
                }
            } catch (CMbException $e) {
                // Rétablissement de l'ancien mot de passe
                $user->_user_password = $old_pwd;
                if ($msg = $user->store()) {
                    CAppUI::stepAjax($msg, UI_MSG_ERROR);
                }

                $e->stepAjax();
                CAppUI::stepAjax("CLDAP-change_password_failed", UI_MSG_ERROR);
            }
        }

        CAppUI::stepAjax("CUser-msg-password-updated", UI_MSG_OK);
        CAppUI::$instance->weak_password = false;
        CAppUI::callbackAjax($callback);

        //CApp::rip();

        return new Response();
    }


    /**
     * @param RequestApi $request
     *
     * @return Response
     * @throws Exception
     * @api public
     */
    public function identicate(RequestApi $request): Response {
        $login = $request->getRequest()->query->get('login');
        if ($login === null) {
            throw new CMbException('No login send to test.');
        }

        $user                = new CUser();
        $user->user_username = $login;
        $user->loadMatchingObjectEsc();

        $data = [
            'login'         => $login,
            'is_identicate' => $user->_id ? true : false
        ];

        $res = new Item($data);
        $res->setType('identicate');

        return $this->renderApiResponse($res);
    }

    /**
     * Get token
     *
     * @param RequestApi $request_api
     *
     * @return Response
     * @throws ApiException
     * @throws Exception
     * @api
     */
    public function getTokens(RequestApi $request_api): Response
    {
        // search hash only use EQUAL filter
        $request_filters = $request_api->getRequestFilter();
        $filter_hash_equal = $request_filters->getFilter('hash', RequestFilter::FILTER_EQUAL);
        if ($filter_hash_equal) {
            $ds                 = CSQLDataSource::get('std');
            $where_datetime_end = "datetime_end " . $ds->prepare('> ?', CMbDT::dateTime())
                . 'OR datetime_end IS NULL';

            $where = [
                $request_filters->getSqlFilter($filter_hash_equal, $ds),
                "($where_datetime_end)",
            ];

            $token = new CViewAccessToken();
            $token->loadObject($where, $request_api->getSortAsSql('datetime_start DESC'));
        } else {
            // load else create token
            $user  = CUser::get();
            $token = CUser::getAccessToken($user->user_id);
        }

        if (!$token->_id) {
            throw new HttpException(Response::HTTP_NOT_FOUND);
        }

        $private_key = CMbSecurity::hash(CMbSecurity::SHA1, $token->hash . '-' . $token->datetime_end);

        // Create item
        $item = Item::createFromRequest($request_api, $token);
        $item->addAdditionalDatas(['key' => $private_key ?? null]);

        return $this->renderApiResponse($item);
    }
}
