<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\Convention;

use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

/**
 * Class Grouping
 *
 * @package Ox\Mediboard\Jfse\ViewModels\Convention
 */
class CGrouping extends CJfseViewModel
{
    /** @var int */
    public $grouping_id;
    /** @var string */
    public $amc_number;
    /** @var string */
    public $amc_label;
    /** @var string */
    public $convention_type;
    /** @var string */
    public $convention_type_label;
    /** @var string */
    public $secondary_criteria;
    /** @var string */
    public $signer_organization_number;
    /** @var int */
    public $group_id;
    /** @var int */
    public $jfse_id;

    public function getProps(): array
    {
        $props                               = parent::getProps();
        $props["grouping_id"]                = 'num';
        $props["amc_number"]                 = 'str';
        $props["amc_label"]                  = 'str';
        $props["convention_type"]            = 'str';
        $props["convention_type_label"]      = 'str';
        $props["secondary_criteria"]         = 'str';
        $props["signer_organization_number"] = 'str';
        $props["group_id"]                   = 'num';
        $props["jfse_id"]                    = 'num';

        return $props;
    }
}
