<?php
/**
 * @package Mediboard\Core\Sessions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Sessions;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Mediboard\System\CPreferences;

/**
 * Session handler container
 */
abstract class CSessionHandler
{
    /** @var ISessionHandler */
    static private $engine;

    /** @var bool Is the session started ? */
    static private $started = false;

    /** @var int Session life time is seconds */
    static private $lifetime;

    static $read_end      = 0;
    static $acquire_start = 0;
    static $acquire_end   = 0;

    /** @var string */
    private static $session_id;

    /** @var [] */
    private static $session_data;

    static $availableEngines = [
        "files" => CFilesSessionHandler::class,
        "mysql" => CMySQLSessionHandler::class,
        "redis" => CRedisSessionHandler::class,
    ];

    /**
     * Init the correct session handler
     *
     * @param string $engine_name Engine name
     *
     * @return void
     */
    static function setHandler($engine_name = "files")
    {
        if (!isset(self::$availableEngines[$engine_name])) {
            $engine_name = "files";
        }

        /** @var ISessionHandler $engine */
        $engine = new self::$availableEngines[$engine_name];


        if (!$engine->init()) {
            $engine = new self::$availableEngines["files"];
            $engine->init();
        }

        if ($engine->useUserHandler()) {
            session_set_save_handler(
                [CSessionHandler::class, "onOpen"],
                [CSessionHandler::class, "onClose"],
                [CSessionHandler::class, "onRead"],
                [CSessionHandler::class, "onWrite"],
                [CSessionHandler::class, "onDestroy"],
                [CSessionHandler::class, "onGC"]
            );
        }

        self::$engine = $engine;
    }

    /**
     * Get session life time
     *
     * @return int
     */
    static function getLifeTime()
    {
        return self::$lifetime;
    }

    /**
     * Sets user defined session life time
     *
     * @return void
     */
    static function setUserDefinedLifetime()
    {
        $lifetime       = self::getSessionMaxLifetime();
        self::$lifetime = $lifetime;

        self::$engine->setLifeTime($lifetime);
    }

    /**
     * Get PHP default session life time value
     *
     * @return int
     */
    static function getPhpSessionLifeTime()
    {
        return (int)ini_get("session.gc_maxlifetime");
    }

    /**
     * Gets the max session lifetime in seconds considering user preference value
     *
     * @return int
     */
    static function getSessionMaxLifetime()
    {
        // Global case when user is logged in
        //        if ($auth = CAppUI::getLastUserAuthentication()) {
        //            return (int)$auth->session_lifetime;
        //        }

        $session_lifetime = null;
        if (CAppUI::$instance && CAppUI::$instance->user_id) {
            $pref             = CPreferences::getPref('sessionLifetime');
            $session_lifetime = $pref['used'] ? $pref['used'] * 60 : null;
        }

        return ($session_lifetime) ?: self::getPhpSessionLifeTime();
    }

    /**
     * Session Open handler
     *
     * @return bool
     */
    static function onOpen()
    {
        return self::$engine->open();
    }

    /**
     * Session Open handler
     *
     * @return bool
     */
    static function onClose()
    {
        return self::$engine->close();
    }

    /**
     * Session Read handler
     *
     * @param string $id Session ID
     *
     * @return bool
     */
    static function onRead($id)
    {
        return self::$engine->read($id);
    }

    /**
     * Session Write handler
     *
     * @param string $id   Session ID
     * @param string $data Session data
     *
     * @return bool
     */
    static function onWrite($id, $data)
    {
        if (!CAppUI::reviveSession()) {
            return true;
        }

        return self::$engine->write($id, $data);
    }

    /**
     * Session Destroy handler
     *
     * @param string $id Session ID
     *
     * @return bool
     */
    static function onDestroy($id)
    {
        CAppUI::setUserAuthExpirationDatetime(CMbDT::dateTime(), $id);

        return self::$engine->destroy($id);
    }

    /**
     * Session GC handler
     *
     * @param int $max Max life time
     *
     * @return bool
     */
    static function onGC($max)
    {
        return self::$engine->gc($max);
    }

    /**
     * Start the session
     *
     * @return void
     */
    static function start(): string
    {
        if (self::$started) {
            return self::$session_id;
        }

        self::$acquire_start = microtime(true);

        // Force the cookie as accessible only through the HTTP protocol (not by js)
        ini_set('session.cookie_httponly', '1');

        session_start();

        // Save informations to access after destruct session (long_request)
        self::$session_id   = session_id();
        self::$session_data = $_SESSION;

        if (!self::$engine->useUserHandler()) {
            self::$acquire_end = microtime(true);
        }

        self::$read_end = microtime(true);

        self::$started = true;

        return self::$session_id;
    }

    /**
     * Ends the session
     *
     * @param bool $destroy Destroy the session data
     *
     * @return void
     */
    static function end($destroy = false)
    {
        if (!self::$started) {
            self::start();
        }

        // Free the session data
        session_unset();

        if ($destroy) {
            @session_destroy(); // Escaped because of an unknown error
        }

        self::$started = false;
    }

    /**
     * Saves session and closes it
     *
     * @return void
     */
    static function writeClose()
    {
        if (!self::$started) {
            return;
        }

        session_write_close();

        self::$started = false;
    }

    /**
     * Tells if the session is currently open
     *
     * @return bool
     */
    static function isOpen()
    {
        return self::$started;
    }

    /**
     * Gets session wait and session read times
     *
     * @return array
     */
    static function getDurations()
    {
        return [
            self::$acquire_end - self::$acquire_start,
            self::$read_end - self::$acquire_end,
        ];
    }

    /**
     * Check wether the session exists or not
     *
     * @param string $session_id The session id
     *
     * @return bool
     */
    static function exists($session_id)
    {
        return self::$engine->exists($session_id);
    }

    /**
     * Destroy session with the session id
     *
     * @param string $session_id The session id
     *
     * @return bool
     */
    static function destroy($session_id)
    {
        CAppUI::setUserAuthExpirationDatetime(CMbDT::dateTime(), $session_id);

        return self::$engine->destroy($session_id);
    }

    /**
     * @return ISessionHandler
     */
    static function getEngine()
    {
        return self::$engine;
    }

    /**
     * @return mixed
     */
    public static function getSessionId(): ?string
    {
        return self::$session_id;
    }

    /**
     * @return array
     */
    public static function getSessionDatas(): array
    {
        return is_array(self::$session_data) ? self::$session_data : [];
    }
}
