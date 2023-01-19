<?php

/**
 * @package Mediboard\Core\Elastic
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Elastic;

use Ox\Core\Elastic\IndexLifeManagement\ElasticIndexLifeManager;

/**
 * Define an Index settings
 * Create the configuration of the index
 */
class ElasticObjectSettings
{
    private string                      $index_name;
    private string                      $config_dsn;
    private int                         $shards;
    private int                         $replicas;
    private ?string                  $ilm_name = null;
    private ?ElasticIndexLifeManager $ilm      = null;

    /**
     * @param string $config_dsn
     *
     * @throws Exceptions\ElasticClientException
     */
    public function __construct(string $config_dsn)
    {
        $this->index_name = ElasticIndexManager::get($config_dsn)->getIndexName();
        $this->config_dsn = $config_dsn;
        $this->shards     = 3;
        $this->replicas   = 1;
    }


    /**
     * This function return the settings used to define an index
     * With the number of shards and replicas.
     * With the ILM name and the rollover alias.
     *
     * @return array
     */
    public function getElasticSettings(): array
    {
        $settings = [
            "number_of_shards"   => $this->shards,
            "number_of_replicas" => $this->replicas,
        ];

        if ($this->ilm_name !== null) {
            $settings["lifecycle"]["name"]           = $this->ilm_name;
            $settings["lifecycle"]["rollover_alias"] = $this->getAliasName();
        }

        return $settings;
    }


    /**
     * @return ElasticIndexLifeManager|null
     */
    public function getIndexLifeManagement(): ?ElasticIndexLifeManager
    {
        return $this->ilm;
    }

    /**
     * @return bool
     */
    public function hasIndexLifeManagement(): bool
    {
        return $this->ilm !== null;
    }

    /**
     * @param string $index_name
     */
    public function setIndexName(string $index_name): void
    {
        $this->index_name = $index_name;
    }

    /**
     * @param int $shards
     *
     * @return $this
     */
    public function setShards(int $shards): self
    {
        $this->shards = $shards;

        return $this;
    }

    /**
     * @param int $replicas
     *
     * @return $this
     */
    public function setReplicas(int $replicas): self
    {
        $this->replicas = $replicas;

        return $this;
    }

    /**
     * Set the index lifecycle management
     *
     * @param ElasticIndexLifeManager $elastic_index_life_management
     *
     * @return $this
     */
    public function addIndexLifeManagement(ElasticIndexLifeManager $elastic_index_life_management): self
    {
        $this->ilm      = $elastic_index_life_management;
        $this->ilm_name = $elastic_index_life_management->getName();

        return $this;
    }

    /**
     * @return string
     */
    public function getIndexName(): string
    {
        return $this->index_name;
    }

    public function getIndexNameAlone(): string
    {
        return $this->index_name . "-alone";
    }

    /**
     * @return string
     */
    public function getFirstIndexName(): string
    {
        return $this->index_name . "-000001";
    }

    /**
     * @return string
     */
    public function getIndexPattern(): string
    {
        return $this->index_name . "-*";
    }

    /**
     * @return string
     */
    public function getAliasName(): string
    {
        return $this->index_name . "-alias";
    }

    /**
     * @return string
     */
    public function getTemplateName(): string
    {
        return $this->index_name . "-template";
    }

    /**
     * @return string
     */
    public function getILMName(): string
    {
        return $this->index_name . "-ilm";
    }

    /**
     * @return string
     */
    public function getConfigDsn(): string
    {
        return $this->config_dsn;
    }

    /**
     * @return int
     */
    public function getShards(): int
    {
        return $this->shards;
    }

    /**
     * @return int
     */
    public function getReplicas(): int
    {
        return $this->replicas;
    }
}
