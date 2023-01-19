<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Convention;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

final class Grouping extends AbstractEntity
{
    /** @var int */
    protected $grouping_id;
    /** @var string */
    protected $amc_number;
    /** @var string */
    protected $amc_label;
    /** @var string */
    protected $convention_type;
    /** @var string */
    protected $convention_type_label;
    /** @var string */
    protected $secondary_criteria;
    /** @var string */
    protected $signer_organization_number;
    /** @var int */
    protected $group_id;
    /** @var int */
    protected $jfse_id;

    public function getGroupingId(): int
    {
        return $this->grouping_id;
    }

    public function getAmcNumber(): string
    {
        return $this->amc_number;
    }

    public function getAmcLabel(): string
    {
        return $this->amc_label;
    }

    public function getConventionType(): string
    {
        return $this->convention_type;
    }

    public function getConventionTypeLabel(): string
    {
        return $this->convention_type_label;
    }

    public function getSecondaryCriteria(): string
    {
        return $this->secondary_criteria;
    }

    public function getSignerOrganizationNumber(): string
    {
        return $this->signer_organization_number;
    }

    public function getGroupId(): int
    {
        return $this->group_id;
    }

    public function getJfseId(): int
    {
        return $this->jfse_id;
    }
}
