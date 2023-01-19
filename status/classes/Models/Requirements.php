<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Status\Models;

class Requirements
{
    private $php_version;
    private $sql_version;
    private $php_extensions;
    private $url_restrictions;
    private $path_access;
    private $pdo;

    /**
     * Requirements constructor.
     *
     * @param PHPVersion      $php_version
     * @param PHPExtension    $php_extensions
     * @param UrlRestriction $url_restrictions
     * @param PathAccess      $path_access
     * @param MySQLVersion    $sql_version
     */
    public function __construct(
        PHPVersion $php_version,
        PHPExtension $php_extensions,
        UrlRestriction $url_restrictions,
        PathAccess $path_access,
        MySQLVersion $sql_version
    ) {
        $this->php_version      = $php_version;
        $this->php_extensions   = $php_extensions;
        $this->url_restrictions = $url_restrictions;
        $this->path_access      = $path_access;
        $this->sql_version      = $sql_version;
    }

    /**
     * @return PHPVersion
     */
    public function getPhpVersion(): PHPVersion
    {
        return $this->php_version->getAll();
    }

    /**
     * @return MySQLVersion
     */
    public function getSqlVersion(): MySQLVersion
    {
        return $this->sql_version->getAll();
    }

    /**
     * @return Prerequisite[]
     */
    public function getPhpExtensions(): array
    {
        return $this->php_extensions->getAll();
    }

    /**
     * @return Prerequisite[]
     */
    public function getUrlRestrictions(): array
    {
        return $this->url_restrictions->getAll();
    }

    /**
     * @return Prerequisite[]
     */
    public function getPathAccess(): array
    {
        return $this->path_access->getAll();
    }
}
