<?php

/**
 * @package Mediboard\Install
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli;

use PDO;
use PDOException;

/**
 * PDO encapsulation for cli scripts
 */
class CommandLinePDO extends PDO
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
     * @param string $database
     *
     * @return bool
     */
    public function isDatabaseExists(string $database): bool
    {
        $statement = $this->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :database');
        $statement->bindValue(':database', $database, PDO::PARAM_STR);
        $statement->execute();
        $count = $statement->fetchColumn();

        return (int) $count === 1;
    }

    /**
     * @param string $database
     *
     * @param bool   $grant_privilege
     *
     * @return false|int
     */
    public function createDatabase($database, $grant_privilege = false)
    {
        $query = "CREATE DATABASE `{$database}`;";

        if ($grant_privilege) {
            $query .= " GRANT ALL PRIVILEGES ON *.* TO 'admin'@'localhost';";
        }

        return !($this->exec($query) === false);
    }

    /**
     * @param null $prefix
     *
     * @return array
     */
    public function getAllDatabases($prefix = null)
    {
        $statement = $this->query('SHOW DATABASES');
        $databases = $statement->fetchAll(PDO::FETCH_COLUMN);
        if ($prefix) {
            foreach ($databases as $key => $database) {
                if (strpos($database, $prefix) !== 0) {
                    unset($databases[$key]);
                }
            }
        }

        return $databases;
    }

    /**
     * @param $database
     *
     * @return false|int
     */
    public function dropDatabase($database)
    {
        return !($this->exec("DROP DATABASE IF EXISTS {$database};") === false);
    }

    /**
     * @return bool
     */
    public function createTables(): bool
    {
        $path    = __DIR__ . '/../sql/mediboard.sql';
        $queries = $this->queryDump($path);
        foreach ($queries as $query) {
            if ($this->exec($query) === false) {
                throw new PDOException(implode(' ', $this->errorInfo()));
            }
        }

        return true;
    }

    /**
     * @param string $salt
     * @param string $password
     *
     * @return bool
     */
    public function updateUsers($salt, $password): bool
    {
        $statement = $this->prepare(
            'UPDATE users SET user_password = :user_password, user_salt = :user_salt
                                 WHERE user_id = :user_id and user_username = :user_name'
        );
        $statement->bindValue(':user_password', $password, PDO::PARAM_STR);
        $statement->bindValue(':user_salt', $salt, PDO::PARAM_STR);
        $statement->bindValue(':user_id', 1, PDO::PARAM_INT);
        $statement->bindValue(':user_name', 'admin', PDO::PARAM_STR);

        return $statement->execute();
    }

    /**
     * Query a whole dump on data source
     *
     * @param string $path File path of the dump
     *
     * @return array
     */
    private function queryDump($path): array
    {
        $lines   = file($path);
        $queries = [];

        $query = null;
        foreach ($lines as $_line) {
            $_line = trim($_line);

            // Ignore empty lines
            if (!$_line) {
                continue;
            }

            // Ignore comments
            if (strpos($_line, '--') === 0 || strpos($_line, '#') === 0) {
                continue;
            }

            // Append line to query
            $query .= $_line;

            // Execute only if query is terminated by a semicolumn
            if (preg_match("/;\s*$/", $_line)) {
                $queries[] = $query;
                $query     = null;
            }
        }

        return $queries;
    }
}
