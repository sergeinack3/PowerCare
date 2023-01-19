<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin\Services;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbSecurity;
use Ox\Core\CMbString;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\CViewAccessToken;
use Ox\Mediboard\Admin\Exception\CouldNotActivateAccount;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceSMTP;
use Throwable;

/**
 * Service handling the activation process of an account (token generation, email sending, etc.)
 */
class AccountActivationService
{
    private const SOURCE_NAME_ACCOUNT_ACTIVATION = 'account-activation';
    private const TOKEN_LIFETIME                 = '+ 3 weeks';

    /** @var CUser */
    private $user;

    /** @var CSourceSMTP|null */
    private $source;

    /**
     * @param CUser            $user
     * @param CSourceSMTP|null $source
     *
     * @throws CouldNotActivateAccount
     */
    public function __construct(CUser $user, ?CSourceSMTP $source = null)
    {
        if (!$user->_id) {
            throw CouldNotActivateAccount::userNotFound();
        }

        if ($user->isSuperAdmin()) {
            throw CouldNotActivateAccount::superAdminNotAllowed();
        }

        $this->user   = $user;
        $this->source = $source;
    }

    /**
     * @throws CouldNotActivateAccount
     */
    private function resetPassword(): void
    {
        try {
            $this->user->_user_password        = CMbSecurity::getRandomPassword();
            $this->user->force_change_password = 1;
            $this->user->allow_change_password = 1;
            $this->user->_is_changing          = 1;

            if ($msg = $this->user->store()) {
                throw new Exception($msg);
            }
        } catch (Throwable $t) {
            throw CouldNotActivateAccount::unableToResetPassword($t->getMessage());
        }
    }

    /**
     * @return CViewAccessToken
     * @throws CouldNotActivateAccount
     */
    public function generateToken(): CViewAccessToken
    {
        $this->resetPassword();

        try {
            $token                 = new CViewAccessToken();
            $token->user_id        = $this->user->_id;
            $token->datetime_start = CMbDT::dateTime();
            $token->datetime_end   = CMbDT::dateTime(self::TOKEN_LIFETIME);
            $token->purgeable      = 1;
            $token->params         = "m=admin\na=chpwd";

            $this->storeToken($token);
        } catch (Throwable $t) {
            throw CouldNotActivateAccount::unableToCreateToken($t->getMessage());
        }

        return $token;
    }

    /**
     * For mockability.
     *
     * @param CViewAccessToken $token
     *
     * @throws Exception
     */
    protected function storeToken(CViewAccessToken $token): void
    {
        if ($msg = $token->store()) {
            throw new Exception($msg);
        }
    }

    /**
     * @param string $email
     *
     * @return bool
     * @throws CouldNotActivateAccount
     */
    public function sendTokenViaEmail(string $email, CSmartyDP $smarty): bool
    {
        if ($this->source === null || !$this->source->_id) {
            throw CouldNotActivateAccount::sourceNotFound();
        }

        if (!$this->source->active) {
            throw CouldNotActivateAccount::sourceNotEnabled();
        }

        // Should be done by Email service...
        if (!$email || !CMbString::checkEmailFormat($email)) {
            throw CouldNotActivateAccount::invalidEmail($email);
        }

        $token = $this->generateToken();

        try {
            $product = CAppUI::conf('product_name');
            $subject = "Bienvenue sur {$product}";

            $smarty->assign('user', $this->user);
            $smarty->assign('email', $email);
            $smarty->assign('product', $product);
            $smarty->assign('token', $token->getUrl());
            $body = $smarty->fetch('activation_link');

            return CApp::sendEmail($subject, $body, null, null, null, [$email], $this->source, false);
        } catch (Throwable $t) {
            throw CouldNotActivateAccount::unableToSendEmail($t->getMessage());
        }
    }

    /**
     * @return CSourceSMTP|null
     */
    public static function getSMTPSource(): ?CSourceSMTP
    {
        return CExchangeSource::get(
            self::SOURCE_NAME_ACCOUNT_ACTIVATION,
            CSourceSMTP::TYPE,
            true,
            null,
            false
        );
    }
}
