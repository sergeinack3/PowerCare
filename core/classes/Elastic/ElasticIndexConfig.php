<?php

/**
 * @package Mediboard\Core\Elastic
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Elastic;

/**
 * Define an Index configuration
 * This class build the url to establish the connection to Elasticsearch
 */
class ElasticIndexConfig
{
    private string $host;
    private int    $port;
    private string $user;
    private string $password;
    private string $connection_type;
    private string $directory;


    /**
     * @param string $host
     * @param int    $port
     * @param string $user
     * @param string $password
     * @param string $connection_type
     */
    public function __construct(
        string $host,
        int $port,
        string $user,
        string $password
    ) {
        $this->port     = $port;
        $this->user     = $user;
        $this->password = $password;

        $this->host            = $host;
        $this->connection_type = "http";
        $this->directory       = "";
        if (preg_match_all(
            "/^((?P<connection_type>http|https):\/\/)?(?P<host>.*?)(?P<directory>\/.*)?$/",
            $host,
            $matches
        )) {
            $this->host = $matches["host"][0];
            if (array_key_exists("connection_type", $matches) && $matches["connection_type"][0] != "") {
                $this->connection_type = $matches["connection_type"][0];
            }
            if (array_key_exists("directory", $matches)) {
                $this->directory = $matches["directory"][0];
            }
        }
    }

    /**
     * This method prepare the host for the Elastic Client
     * Using the other method provided by Elastic Search causes huge waiting time
     *
     * @return string[]
     */
    public function getConnectionParams(): array
    {
        $_host = $this->connection_type . "://" . $this->user . ":" . $this->password . "@" . $this->host . ":" . $this->port . $this->directory;

        return [$_host];
    }

    /**
     * @return String
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getPort(): string
    {
        return $this->port;
    }

    /**
     * @return String
     */
    public function getUser(): string
    {
        return $this->user;
    }
}
