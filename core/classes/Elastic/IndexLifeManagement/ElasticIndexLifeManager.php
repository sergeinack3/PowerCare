<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Elastic\IndexLifeManagement;

use Ox\Core\Elastic\IndexLifeManagement\Exceptions\ElasticPhaseConfigurationException;
use Ox\Core\Elastic\IndexLifeManagement\Phases\ElasticColdPhase;
use Ox\Core\Elastic\IndexLifeManagement\Phases\ElasticDeletePhase;
use Ox\Core\Elastic\IndexLifeManagement\Phases\ElasticHotPhase;
use Ox\Core\Elastic\IndexLifeManagement\Phases\ElasticWarmPhase;

/**
 * Index Lifecycle Management (ILM) policies to automatically manage indices according to your performance, resiliency,
 * and retention requirements. This class will define the 4 phases of an ILM.
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/index-lifecycle-management.html
 *       Elasticsearch's Documentation
 */
class ElasticIndexLifeManager
{
    private string              $name;
    private ?ElasticHotPhase    $hot_phase    = null;
    private ?ElasticWarmPhase   $warm_phase   = null;
    private ?ElasticColdPhase   $cold_phase   = null;
    private ?ElasticDeletePhase $delete_phase = null;


    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return array
     * @throws ElasticPhaseConfigurationException
     */
    public function build(): array
    {
        $phases = [];

        if ($this->hot_phase === null) {
            throw new ElasticPhaseConfigurationException(
                "ElasticPhaseConfigurationException-error-To configure an ILM, you need a hot phase."
            );
        }
        $phases["hot"] = $this->hot_phase->build();

        if ($this->warm_phase !== null) {
            $phases["warm"] = $this->warm_phase->build();
        }

        if ($this->cold_phase !== null) {
            $phases["cold"] = $this->cold_phase->build();
        }

        if ($this->delete_phase !== null) {
            $phases["delete"] = $this->delete_phase->build();
        }

        return [
            "policy" => $this->name,
            "body"   => [
                "policy" => [
                    "phases" => $phases,
                ],
            ],
        ];
    }

    public function setHotPhase(ElasticHotPhase $hot_phase): self
    {
        $this->hot_phase = $hot_phase;

        return $this;
    }

    public function setWarmPhase(ElasticWarmPhase $warm_phase): self
    {
        $this->warm_phase = $warm_phase;

        return $this;
    }

    public function setColdPhase(ElasticColdPhase $cold_phase): self
    {
        $this->cold_phase = $cold_phase;

        return $this;
    }

    public function setDeletePhase(ElasticDeletePhase $delete_phase): self
    {
        $this->delete_phase = $delete_phase;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
