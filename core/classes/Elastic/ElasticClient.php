<?php

/**
 * @package Mediboard\Core\Elastic
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Elastic;

use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\Curl\CouldNotConnectToHost;
use Elasticsearch\Common\Exceptions\ElasticsearchException;
use Elasticsearch\Common\Exceptions\NoNodesAvailableException;
use Elasticsearch\Namespaces\{AbstractNamespace, IlmNamespace, IndicesNamespace, NodesNamespace};
use Exception;
use Ox\Core\Chronometer;
use Ox\Core\Elastic\Exceptions\ElasticClientException;
use Ox\Core\Elastic\Exceptions\ElasticException;

/**
 * @method bool ping(array $array = [])
 * @method array info(array $array = [])
 * @method array bulk(array $array = [])
 * @method array delete(array $array = [])
 * @method array deleteByQuery(array $array = [])
 * @method array index(array $array = [])
 * @method array count(array $array = [])
 * @method array search(array $array = [])
 * @method array get(array $array = [])
 * @method array scroll(array $array = [])
 * @method array update(array $array = [])
 * @method IlmNamespace ilm()
 * @method NodesNamespace nodes()
 * @method IndicesNamespace indices()
 */
class ElasticClient
{
    private static ?Chronometer $chrono      = null;
    private static array        $log_entries = [];

    private Client  $client;
    private ?string $namespace;

    public function __construct(Client $client)
    {
        $this->client    = $client;
        $this->namespace = null;
        if (!static::$chrono) {
            static::$chrono = new Chronometer();
        }
    }

    /**
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     * @throws ElasticsearchException
     * @throws ElasticException
     */
    public function __call(string $method, array $args = [])
    {
        $is_chrono_started = true;
        $result            = [];
        try {
            static::$chrono->start();
            if ($this->namespace) {
                $result = $this->client->{$this->namespace}()->{$method}($args[0] ?? []);
            } else {
                $result = $this->client->{$method}($args[0] ?? []);
            }
            if ($result instanceof AbstractNamespace) {
                self::$chrono->abort();
                $is_chrono_started = false;
                $that              = clone $this;
                $that->namespace   = $method;

                return $that;
            }
        } catch (ElasticsearchException $e) {
            $this->manageElasticExceptions($e);
        } finally {
            if ($is_chrono_started) {
                static::$chrono->stop();

                $base_url = "/";
                $base_url .= $this->namespace ? $this->namespace . "/" . $method : $method;
                if (array_key_exists(0, $args)) {
                    $args = $args[0];
                    if (array_key_exists("index", $args)) {
                        $index = $args["index"];
                        unset($args["index"]);
                        $base_url .= "/" . $index;
                    }
                }

                self::$log_entries[] = [$base_url , round(static::$chrono->latestStep * 1000, 2)];
            }
        }

        return $result;
    }

    /**
     * @param Exception $e
     *
     * @return mixed
     * @throws ElasticClientException
     * @throws ElasticsearchException
     */
    private function manageElasticExceptions(ElasticsearchException $e)
    {
        switch (true) {
            case $e instanceof CouldNotConnectToHost:
            case $e instanceof NoNodesAvailableException:
                throw new ElasticClientException("ElasticIndexManager-error-Connection failed");
            default:
                throw $e;
        }
    }

    public static function getChrono(): ?Chronometer
    {
        return self::$chrono;
    }

    /**
     * @return array
     */
    public static function getLogEntries(): array
    {
        return self::$log_entries;
    }
}
