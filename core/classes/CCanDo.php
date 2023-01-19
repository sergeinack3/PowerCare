<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Ox\Core\Kernel\Exception\PublicEnvironmentException;
use Ox\Mediboard\Admin\CPermModule;
use Ox\Mediboard\Admin\CPermObject;
use Symfony\Component\HttpFoundation\Response;

/**
 * CanDo class
 *
 * Allow to check permissions on a module with redirect helpers
 */
class CCanDo
{
    /** @var bool */
    public $read;

    /** @var bool */
    public $edit;

    /** @var bool */
    public $view;

    /** @var bool */
    public $admin;

    /** @var string */
    public $context;

    /** @var string|array Should not be used, find another redirection behavioural session mangagement */
    public $setValues;

    /**
     * Access denied, will stop current request and send an HTTP 403
     *
     * @return void
     */
    public function denied(): void
    {
        CApp::failIfPublic();

        if ($this->setValues) {
            if (is_scalar($this->setValues)) {
                CValue::setSession($this->setValues);
            } else {
                foreach ($this->setValues as $key => $value) {
                    CValue::setSession($key, $value);
                }
            }
        }

        CAppUI::accessDenied($this->context);
    }

    /**
     * Check if the connected user has READ rights on the current page
     *
     * @param mixed $setValues Values to set in session
     *
     * @return void
     */
    public function needsRead($setValues = null): void
    {
        CApp::failIfPublic();

        $this->setValues = $setValues;
        if (!$this->read) {
            $this->context .= " read permission";
            $this->denied();
        }
    }

    /**
     * Check if the connected user has EDIT rights on the current page
     *
     * @param mixed $setValues Values to set in session
     *
     * @return void
     */
    public function needsEdit($setValues = null): void
    {
        CApp::failIfPublic();

        $this->setValues = $setValues;

        if (!$this->edit) {
            $this->context .= " edit permission";
            $this->denied();
        }
    }

    /**
     * Check if the connected user has ADMIN rights on the current page
     *
     * @param mixed $setValues Values to set in session
     *
     * @return void
     */
    public function needsAdmin($setValues = null): void
    {
        CApp::failIfPublic();

        $this->setValues = $setValues;

        if (!$this->admin) {
            $this->context .= " admin permission";
            $this->denied();
        }
    }

    /**
     * Check if the connected user has READ rights on the current page
     *
     * @return void
     */
    public static function checkRead(): void
    {
        CApp::failIfPublic();

        global $can;
        $can->needsRead();
    }

    /**
     * Return the global READ permission
     *
     * @return bool
     */
    public static function read()
    {
        CApp::failIfPublic();

        global $can;

        return $can->read;
    }

    /**
     * Check if the connected user has EDIT rights on the current page
     *
     * @return void
     */
    public static function checkEdit(): void
    {
        CApp::failIfPublic();

        global $can;
        $can->needsEdit();
    }

    /**
     * Return the global EDIT permission
     *
     * @return bool
     */
    public static function edit()
    {
        CApp::failIfPublic();

        global $can;

        return $can->edit;
    }

    /**
     * Check if the connected user has ADMIN rights on the current page
     *
     * @return void
     */
    public static function checkAdmin(): void
    {
        CApp::failIfPublic();

        global $can;
        $can->needsAdmin();
    }

    /**
     * Return the global ADMIN permission
     *
     * @return bool
     */
    public static function admin()
    {
        CApp::failIfPublic();

        global $can;

        return $can->admin;
    }

    /**
     * Dummy check method with no control
     * Enables differentiation between no-check and undefined-check views
     *
     * @return void
     */
    public static function check(): void
    {
        CApp::failIfPublic();
    }

    /**
     * Get permissions as array
     *
     * @param CPermModule|CPermObject $perm Permission level
     *
     * @return array
     */
    public static function getPerms($perm): array
    {
        $perms = [
            'read' => false,
            'edit' => false,
            'deny' => false,
        ];

        if ($perm->permission === '0') {
            $perms['deny'] = true;

            return $perms;
        }

        $perms['read'] = ($perm->permission >= '1');
        $perms['edit'] = ($perm->permission === '2');

        return $perms;
    }
}
