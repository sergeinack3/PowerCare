<?php

/**
 * @package Mediboard\Core\Elastic
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Elastic;

use DateTimeImmutable;
use Elasticsearch\ClientBuilder;
use Exception;
use Ox\Core\CAppUI;
use Ox\Core\Elastic\Exceptions\ElasticClientException;
use Throwable;

/**
 * Basic Elastic Search client Wrapper
 */
class ElasticIndexManager
{
    private const DEFAULT_ELASTIC_CONNECTION_TIMEOUT = 2;
    private const DEFAULT_ELASTIC_TIMEOUT            = 2;

    private ElasticClient      $client;
    private string             $dsn;
    private string             $index_name;
    private ElasticIndexConfig $config;

    public function __construct(ElasticClient $client, string $dsn, string $index_name, ElasticIndexConfig $config)
    {
        $this->client     = $client;
        $this->dsn        = $dsn;
        $this->index_name = $index_name;
        $this->config     = $config;
    }

    /**
     * Get the data source with given name.
     * Create it if necessary
     *
     * @param string                  $dsn Data source name
     * @param ElasticIndexConfig|null $index_config
     *
     * @return ElasticIndexManager
     * @throws ElasticClientException
     */
    public static function get(string $dsn, ElasticIndexConfig $index_config = null): ElasticIndexManager
    {
        try {
            $configuration = CAppUI::conf("elastic $dsn");
            if (!is_array($configuration) || !array_key_exists("elastic_host", $configuration)) {
                throw new Exception();
            }
        } catch (Throwable $t) {
            throw new ElasticClientException(
                CAppUI::tr("ElasticIndexManager-error-Can not create Elasticsearch client without dsn configuration")
            );
        }

        if ($index_config) {
            $config = $index_config;
        } else {
            $config = new ElasticIndexConfig(
                $configuration["elastic_host"],
                (isset($configuration["elastic_port"]) && $configuration["elastic_port"])
                    ? $configuration["elastic_port"] : 9200,
                (isset($configuration["elastic_user"]) && $configuration["elastic_user"])
                    ? $configuration["elastic_user"] : "",
                (isset($configuration["elastic_pass"]) && $configuration["elastic_pass"])
                    ? $configuration["elastic_pass"] : "",
            );
        }

        $options = [
            "client" => [
                "curl" => [
                    CURLOPT_CONNECTTIMEOUT => $configuration["elastic_curl-connection-timeout"] ?? self::DEFAULT_ELASTIC_CONNECTION_TIMEOUT,
                    CURLOPT_TIMEOUT        => $configuration["elastic_curl-timeout"] ?? self::DEFAULT_ELASTIC_TIMEOUT,
                ],
            ],
        ];

        $retries = (int)($configuration['elastic_connection-retries'] ?? 1);

        try {
            $client         = ClientBuilder::create()
                ->setHosts($config->getConnectionParams())
                ->setConnectionParams($options)
                ->setRetries($retries)
                ->build();
            $elastic_client = new ElasticClient($client);
        } catch (Throwable $t) {
            throw new ElasticClientException($t->getMessage());
        }

        $index = $configuration["elastic_index"] ?? "";

        if ($index === "") {
            throw new ElasticClientException(
                "ElasticIndexManager-error-Can not create Elasticsearch client without index name"
            );
        }

        return new self($elastic_client, $dsn, $index, $config);
    }

    /**
     * @return string
     */
    public function getIndexName(): string
    {
        return $this->index_name;
    }

    public function getServerStatus(): array
    {
        if (!$this->isOnline()) {
            return [
                "errors" => [
                    [
                        "type"    => 1,
                        "message" => "No active node running on that host",
                    ],
                ],
            ];
        }

        $info_server = $this->client->info();
        $host        = "";
        $nodes       = [];
        foreach ($this->client->nodes()->info()["nodes"] as $node) {
            $date                 = new DateTimeImmutable();
            $date                 = $date->setTimestamp((int) ($node["jvm"]["start_time_in_millis"] / 1000));
            $nodes[$node["name"]] = [
                "ip"           => $node["ip"],
                "version"      => $node["version"],
                "roles"        => $node["roles"],
                "java_version" => $node["jvm"]["version"],
                "date_start"   => $date->format("d/m/Y H:i:s"),
                "memory"       => $node["jvm"]["mem"]["heap_max_in_bytes"],
            ];

            if ($node["name"] == $info_server["name"]) {
                $host = $node["http"]["publish_address"];
            }
        }

        return [
            "nodes"  => $nodes,
            "server" => [
                "elected"               => $host,
                "online"                => true,
                "cluster_name"          => $info_server["cluster_name"],
                "elasticsearch_version" => $info_server["version"]["number"],
                "lucene_version"        => $info_server["version"]["lucene_version"],
            ],
        ];
    }

    public function getStatus(ElasticObject $object): array
    {
        $server_status = $this->getServerStatus();
        if (array_key_exists("errors", $server_status)) {
            return $server_status;
        }

        $manager = ElasticObjectManager::getInstance();

        $index_exists   = $manager::checkIndexExists($object);
        $index_mappings = $manager->getIndexMappings($object);
        foreach ($index_mappings as $name => $mapping) {
            $index_mappings[$name]["mappings"]["properties"] = json_encode(
                $mapping["mappings"]["properties"],
                JSON_PRETTY_PRINT
            );
        }
        $template          = $manager->getIndexTemplate($object);
        $template_mappings = [];
        $template_settings = [];
        if ($template !== []) {
            $template_mappings = json_encode(
                $template["index_templates"][0]["index_template"]["template"]["mappings"],
                JSON_PRETTY_PRINT
            );
            $template_settings = json_encode(
                $template["index_templates"][0]["index_template"]["template"]["settings"],
                JSON_PRETTY_PRINT
            );
        }
        $template_exists = $manager::checkTemplateExists($object);
        $has_ilm         = $object->getSettings()->hasIndexLifeManagement();
        $ilm             = $manager->getILM($object);
        $ilm_exists      = $ilm !== [];


        $status = [
            "index"    => [
                "exists"   => $index_exists,
                "name"     => $object->getSettings()->getIndexPattern(),
                "mappings" => $index_mappings,
            ],
            "template" => [
                "exists"   => $template_exists,
                "name"     => $object->getSettings()->getTemplateName(),
                "mappings" => $template_mappings,
                "settings" => $template_settings,
            ],
            "ilm"      => [
                "has"    => $has_ilm,
                "exists" => $ilm_exists,
                "status" => $ilm,
            ],
        ];

        return array_merge($server_status, $status);
    }

    /**
     * Return if the ElasticSearch is online
     *
     * @return bool
     */
    public function isOnline(): bool
    {
        try {
            return $this->client->ping();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @return ElasticIndexConfig
     */
    public function getConfig(): ElasticIndexConfig
    {
        return $this->config;
    }

    /**
     * @return ElasticClient|null
     */
    public function getClient(): ?ElasticClient
    {
        return $this->client;
    }

    /**
     * @param ElasticClient $client
     */
    public function setClient(ElasticClient $client): void
    {
        $this->client = $client;
    }
}
