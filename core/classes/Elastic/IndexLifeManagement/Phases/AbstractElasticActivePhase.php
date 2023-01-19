<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Elastic\IndexLifeManagement\Phases;

use Ox\Core\Units\ByteSizeUnitEnum;
use stdClass;

/**
 * This abstract class defines the base of an active ILM phase (HOT, WARM, COLD).
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/ilm-actions.html Elasticsearch's Documentation
 */
abstract class AbstractElasticActivePhase extends AbstractElasticPhase
{
    protected ?int               $priority                = null;
    protected bool               $read_only               = false;
    protected bool               $force_merge             = false;
    protected int                $force_merge_max_segment = 0;
    protected bool               $force_merge_compress    = false;
    protected ?ElasticShrinkType $shrink_type             = null;
    protected ?int              $shrink_value      = null;
    protected ?ByteSizeUnitEnum $shrink_value_unit = null;


    public function build(): array
    {
        $array   = parent::build();
        $actions = [];

        if ($this->priority !== null) {
            $actions["set_priority"]["priority"] = $this->priority;
        }

        if ($this->force_merge) {
            $actions["forcemerge"]["max_num_segments"] = $this->force_merge_max_segment;
            if ($this->force_merge_compress) {
                $actions["forcemerge"]["index_codec"] = "best_compression";
            }
        }

        if ($this->shrink_type !== null) {
            if ($this->shrink_type->getValue() === ElasticShrinkType::SHARD_SIZE()->getValue()) {
                $actions["shrink"]["max_primary_shard_size"] = $this->shrink_value . $this->shrink_value_unit;
            } elseif ($this->shrink_type->getValue() === ElasticShrinkType::SHARD_COUNT()->getValue()) {
                $actions["shrink"]["number_of_shards"] = $this->shrink_value;
            }
        }

        if ($this->read_only) {
            $actions["readonly"] = new stdClass();
        }

        if (count($actions) > 0) {
            $array["actions"] = $actions;
        } else {
            $array["actions"] = new stdClass();
        }


        return $array;
    }

    public function setShrinkOnShardCount(int $shards_count): self
    {
        $this->shrink_type  = ElasticShrinkType::SHARD_COUNT();
        $this->shrink_value = $shards_count;

        return $this;
    }

    public function setShrinkOnShardSize(int $shard_size, ByteSizeUnitEnum $shard_size_unit): self
    {
        $this->shrink_type       = ElasticShrinkType::SHARD_SIZE();
        $this->shrink_value      = $shard_size;
        $this->shrink_value_unit = $shard_size_unit;

        return $this;
    }

    public function setForceMerge(int $force_merge_max_segment, bool $force_merge_compress = false): self
    {
        $this->force_merge             = true;
        $this->force_merge_max_segment = $force_merge_max_segment;
        $this->force_merge_compress    = $force_merge_compress;

        return $this;
    }

    public function activeReadOnly(): self
    {
        $this->read_only = true;

        return $this;
    }

    /**
     * @param int $priority
     *
     * @return AbstractElasticPhase
     */
    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }
}
