<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Status;

use Exception;
use Ox\Status\Models\MySQLVersion;
use PDO;

/**
 * PDO encapulation for status scripts
 */
class StatusPDO extends PDO
{
    /**
     * Constructor
     *
     * @param string      $host SQL server hostname
     * @param string      $user Username
     * @param string      $pass Password
     * @param null|string $base Database name
     *
     */
    public function __construct($host, $user, $pass, $base = null)
    {
        $dsn = "mysql:host=$host";

        if ($base) {
            $dsn .= ";dbname=$base";
        }

        parent::__construct($dsn, $user, $pass);
    }

    /**
     * @return array
     */
    public function getInfos(): array
    {
        return [
            'driver' => $this->getAttribute(PDO::ATTR_DRIVER_NAME),
            'client' => $this->getAttribute(PDO::ATTR_CLIENT_VERSION),
            'server' => $this->getAttribute(PDO::ATTR_SERVER_VERSION),
            'status' => $this->getAttribute(PDO::ATTR_CONNECTION_STATUS),
        ];
    }

    /**
     * @return MySQLVersion
     */
    public function createMySqlVersion(): MySQLVersion
    {
        $sql_version                  = new MySQLVersion();
        $sql_version->name            = MySQLVersion::VERSION_REQUIRE;
        $sql_version->mandatory       = true;
        $sql_version->description     = 'Version de MySql';
        $sql_version->current_version = $this->getAttribute(PDO::ATTR_SERVER_VERSION);

        return $sql_version;
    }

    /**
     * @param $table
     *
     * @return bool
     */
    private function tableExists($table)
    {
        try {
            $result = $this->query("SELECT 1 FROM $table LIMIT 1");
        } catch (Exception $e) {
            return false;
        }

        return $result !== false;
    }

    /**
     * @param string $orderby
     * @param int    $offest
     * @param int    $limit
     *
     * @return array
     */
    public function listErrors($orderby, $offest, $limit): array
    {
        if (!$this->tableExists('error_log')) {
            return [];
        }

        $sth = $this->prepare("SELECT * FROM error_log ORDER BY {$orderby} LIMIT :offset,:limit");
        $sth->bindValue(':limit', $limit, PDO::PARAM_INT);
        $sth->bindValue(':offset', $offest, PDO::PARAM_INT);
        $sth->execute();

        return $sth->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * @return int
     */
    public function countErrors(): int
    {
        if (!$this->tableExists('error_log')) {
            return 0;
        }

        return (int)$this->query("SELECT COUNT(*) FROM error_log")->fetchColumn();
    }

    /**
     * @return mixed
     */
    public function getAdminUser()
    {
        $sth = $this->prepare("SELECT * FROM users WHERE user_username = :user_username");
        $sth->bindValue(':user_username', 'admin');
        $sth->execute();

        return $sth->fetch(PDO::FETCH_OBJ);
    }
}
