<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Elastic\IndexLifeManagement\Phases;

use Ox\Core\Elastic\IndexLifeManagement\Exceptions\ElasticPhaseConfigurationException;
use Ox\Core\Units\ByteSizeUnitEnum;
use Ox\Core\Units\TimeUnitEnum;
use stdClass;

/**
 * This class is the representation of the ILM Hot phase.
 * The first different between other phases is the rollover action.
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/ilm-index-lifecycle.html Elasticsearch's Documentation
 */
class ElasticHotPhase extends AbstractElasticActivePhase
{
    protected bool $rollover = false;

    protected ?int             $max_primary_shard_size = null;
    protected ByteSizeUnitEnum $max_primary_shard_size_unit;

    protected ?int         $max_age = null;
    protected TimeUnitEnum $max_age_unit;

    protected ?int $max_documents = null;

    public function __construct()
    {
        $this->min_age      = 0;
        $this->min_age_unit = TimeUnitEnum::MILLISECONDS();
    }

    /**
     * @return array
     * @throws ElasticPhaseConfigurationException
     */
    public function build(): array
    {
        if ($this->rollover) {
            $array = parent::build();
            if ($array["actions"] instanceof stdClass) {
                $array["actions"] = [];
            }
        } else {
            if ($this->force_merge || $this->shrink_type !== null || $this->read_only) {
                throw new ElasticPhaseConfigurationException(
                    "ElasticPhaseConfigurationException-error-Cannot configure force_merge, shrink or readonly without rollover on Hot phase."
                );
            }
            $array            = [];
            $array["min_age"] = $this->min_age . $this->min_age_unit;
            if ($this->priority !== null) {
                $array["actions"]["set_priority"]["priority"] = $this->priority;
            }
        }


        $rollover = [];
        if ($this->max_primary_shard_size !== null) {
            $rollover["max_primary_shard_size"] = $this->max_primary_shard_size . $this->max_primary_shard_size_unit;
        }

        if ($this->max_age !== null) {
            $rollover["max_age"] = $this->max_age . $this->max_age_unit;
        }

        if ($this->max_documents !== null) {
            $rollover["max_docs"] = $this->max_documents;
        }


        $array["actions"]["rollover"] = $rollover;


        return $array;
    }

    public function setRolloverOnPrimaryShardSize(
        int $max_primary_shard_size,
        ByteSizeUnitEnum $max_primary_shard_size_unit
    ): self {
        $this->rollover                    = true;
        $this->max_primary_shard_size      = $max_primary_shard_size;
        $this->max_primary_shard_size_unit = $max_primary_shard_size_unit;

        return $this;
    }

    public function setRolloverOnMaxAge(int $max_age, TimeUnitEnum $max_age_unit): self
    {
        $this->rollover     = true;
        $this->max_age      = $max_age;
        $this->max_age_unit = $max_age_unit;

        return $this;
    }

    public function setRolloverOnMaxDocuments(int $max_documents): self
    {
        $this->rollover      = true;
        $this->max_documents = $max_documents;

        return $this;
    }
}
