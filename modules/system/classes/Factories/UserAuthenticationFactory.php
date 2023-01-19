<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Factories;

use Ox\Core\Auth\Badges\LogAuthBadge;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\System\CUserAgent;
use Ox\Mediboard\System\CUserAuthentication;
use Ox\Mediboard\System\CUserAuthenticationError;

/**
 * Description
 */
class UserAuthenticationFactory
{
    public function createSuccessStateless(CUser $user, LogAuthBadge $log_auth): ?CUserAuthentication
    {
        if (!CUserAuthentication::authReady() || $user->dont_log_connection) {
            return null;
        }

        // Aggregate per day stateless authentications
        $last_auth = $user->loadRefLastAuth();

        if (
            $last_auth
            && ($last_auth->auth_method === $log_auth->getMethod())
            && str_starts_with($last_auth->session_id, 'stateless')
            && (CMbDT::date($last_auth->last_session_update) === CMbDT::date())
        ) {
            $last_auth->last_session_update = CMbDT::dateTime();
            $last_auth->nb_update++;
            $last_auth->expiration_datetime = CMbDT::dateTime();
            $last_auth->store();

            return $last_auth;
        }

        $dtnow = CMbDT::dateTime();

        $auth                      = new CUserAuthentication();
        $auth->user_id             = $user->_id;
        $auth->previous_auth_id    = null;
        $auth->auth_method         = $log_auth->getMethod();
        $auth->datetime_login      = $dtnow;
        $auth->last_session_update = $dtnow;
        $auth->nb_update           = 0;
        $auth->ip_address          = CAppUI::$instance->ip;

        // For Legacy compat
        CAppUI::$instance->auth_method = $log_auth->getMethod();

        // Api mode
        $auth->session_id          = uniqid('stateless', true);
        $auth->session_lifetime    = 0;
        $auth->expiration_datetime = $dtnow;


        // User agent
        $user_agent            = CUserAgent::create(true);
        $auth->user_agent_id   = $user_agent->_id;
        $auth->_ref_user_agent = $user_agent;

        // In order
        CAppUI::$instance->user_prev_login = $user->getLastLogin();

        return $auth;
    }

    /**
     * @param CUser       $user
     * @param string $auth_method
     *
     * @return CUserAuthenticationError
     */
    public function createError(CUser $user, string $auth_method): CUserAuthenticationError
    {
        $error              = new CUserAuthenticationError();
        $error->login_value = $user->user_username;
        $error->user_id     = $user->_id;
        $error->message     = CAppUI::getMsg(false);
        $error->datetime    = CMbDT::dateTime();
        $error->auth_method = $auth_method;
        $error->ip_address  = CAppUI::$instance->ip;
        $error->identifier  = $error::makeIdentifier();

        return $error;
    }
}
