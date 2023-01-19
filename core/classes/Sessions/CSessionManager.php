<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Sessions;

use Exception;
use Jumbojett\OpenIDConnectClientException;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\OAuth2\OIDC\PSC\Client as PscClient;
use Ox\Core\OAuth2\OIDC\FC\Client as FcClient;
use Ox\Core\OAuth2\OIDC\TokenSet;
use RuntimeException;

/**
 * Class CSessionManager
 */
class CSessionManager
{
    /** @var self */
    private static $instance;

    /** @var string */
    private $session_handler;

    /** @var bool */
    private $is_init = false;

    /**
     * CSessionManager constructor.
     *
     * @param string $session_handler
     */
    private function __construct(string $session_handler)
    {
        $this->session_handler = $session_handler;
    }

    public static function get(): self
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        $session_handler = CAppUI::conf("session_handler");
        $instance        = new self($session_handler);
        self::$instance  = $instance;

        return $instance;
    }

    /**
     * @return void
     * @throws OpenIDConnectClientException
     * @throws Exception
     */
    public function init(): void
    {
        if ($this->is_init) {
            throw new RuntimeException('Session is already init');
        }

        // Don't ignore user abort as long as session is still locked
        ignore_user_abort(false);

        // Manage the session variable(s)
        $session_name = $this::forgeSessionName();

        session_name($session_name);

        if (get_cfg_var("session.auto_start") > 0) {
            session_write_close();
        }

        CSessionHandler::setHandler($this->session_handler);

        // Start session
        $session_id = CSessionHandler::start();

        if (function_exists("apache_setenv")) {
            apache_setenv("PHP_SESS_ID", $session_id);
        }

        // Ignore aborted HTTP request, so that PHP finishes the current script
        ignore_user_abort(true);

        // Register shutdown function to end the session
        CApp::registerShutdown([CSessionHandler::class, "writeClose"], CApp::SESSION_PRIORITY);


        // Check if the session was made via a temporary token and save its expiration date
        if (isset($_SESSION["token_session"])) {
            CAppUI::$token_expiration = $_SESSION["token_expiration"];
            CAppUI::$token_session    = true;
            CAppUI::$token_id         = $_SESSION["token_id"];
        }

        // Reset session if it expired
        if (CAppUI::isTokenSessionExpired()) {
            CAppUI::$token_expiration = null;
            CAppUI::$token_session    = false;
            CAppUI::$token_id         = null;

            // Free the session data
            CSessionHandler::end(true);

            // Start it back
            CSessionHandler::start();
        }

        $oidc_tokens = null;

        // If logout, store real expiration datetime in user_auth object
        if (isset($_GET["logout"]) && isset($_SESSION["AppUI"])) {
            if ($_SESSION['AppUI']->oidc_tokens instanceof TokenSet) {
                $oidc_tokens = $_SESSION['AppUI']->oidc_tokens;
            }

            // Use $_SESSION because CAppUI::$instance is not set yet (see below)
            $last_auth = $_SESSION["AppUI"]->_ref_last_auth;

            // Remove the session cookie upon user logout
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 1000, '/');
            }

            if ($last_auth && $last_auth->_id) {
                $dtnow                          = CMbDT::dateTime();
                $last_auth->expiration_datetime = $dtnow;
                $last_auth->last_session_update = $dtnow;
                $last_auth->nb_update++;
                $last_auth->store();
            }
        }

        // Check if session has previously been initialised
        if (empty($_SESSION["AppUI"]) || isset($_GET["logout"])) {
            $_SESSION["AppUI"] = CAppUI::initInstance();
        }

        CAppUI::$instance               =& $_SESSION["AppUI"];
        CAppUI::$instance->session_name = $session_name;
        if (!isset($_SESSION["locked"])) {
            $_SESSION["locked"] = false;
        }

        CAppUI::checkSessionUpdate();

        // Tell to not revive the session on hit
        CAppUI::$session_no_revive = (bool)(($_GET['session_no_revive']) ?? false);

        $this->is_init = true;

        // Logout has been called and oidc_tokens in session, so we call PSC sign out endpoint which redirects the user.
        if (isset($_GET['logout']) && ($oidc_tokens instanceof TokenSet)) {
            switch ($oidc_tokens->getType()) {
                case TokenSet::PSC_TYPE:
                    $provider = new PscClient();
                    $endpoint = $provider->signOut($oidc_tokens->getIdToken(), true);

                    header("Location: {$endpoint}");
                    CApp::rip();
                    break;

                case TokenSet::FC_TYPE:
                    $provider = new FcClient();
                    $endpoint = $provider->signOut($oidc_tokens->getIdToken());

                    header("Location: {$endpoint}");
                    CApp::rip();
                    break;

                default:
                    throw new Exception('Unknown token provider');
            }
        }
    }

    /**
     * @return bool
     */
    public function userHasSession(): bool
    {
        // $instance->user_id can be null or 0
        return (CAppUI::$instance !== null && CAppUI::$instance->user_id !== null && CAppUI::$instance->user_id !== 0);
    }

    /**
     * For mockability purposes.
     *
     * @param string|null $root_dir
     *
     * @return string
     * @throws Exception
     */
    public function getSessionName(string $root_dir = null): string
    {
        return self::forgeSessionName($root_dir);
    }

    /**
     * Create the session name according to application root
     *
     * @param string|null $root_dir Application root directory basename
     *
     * @return string
     * @throws Exception
     */
    public static function forgeSessionName(string $root_dir = null): string
    {
        $root_dir = ($root_dir) ?: CAppUI::conf('root_dir');
        $root_dir = basename($root_dir);

        $session_name = preg_replace('/[^a-z0-9]/i', '', $root_dir);

        if (!preg_match('/[a-z]/i', $session_name, $matches)) {
            $session_name = "mb{$session_name}";
        }

        return $session_name;
    }

    /**
     * @return void
     */
    public function terminate(): void
    {
        if (CApp::isSessionRestricted()) {
            CSessionHandler::end(true);
        } else {
            // Explicit close of the session before object destruction
            CSessionHandler::writeClose();
        }
    }

    public function getSessionHandler(): ?string
    {
        return $this->session_handler;
    }
}
