<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\Convention;

use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

/**
 * Class CConvention
 *
 * @package Ox\Mediboard\Jfse\ViewModels\Convention
 */
class CConvention extends CJfseViewModel
{
    /** @var int */
    public $convention_id;
    /** @var string */
    public $signer_organization_number;
    /** @var string */
    public $signer;
    /** @var string */
    public $convention_type;
    /** @var string */
    public $secondary_criteria;
    /** @var string */
    public $agreement_type;
    /** @var string */
    public $signer_organization_label;
    /** @var string */
    public $amc_number;
    /** @var string */
    public $amc_label;
    /** @var string */
    public $statutory_operator;
    /** @var string */
    public $routing_code;
    /** @var string */
    public $host_id;
    /** @var string */
    public $domain_name;
    /** @var string */
    public $sts_referral_code;
    /** @var int */
    public $group_convention_flag;
    /** @var int */
    public $certificate_use_flag;
    /** @var int */
    public $sts_disabled_flag;
    /** @var int */
    public $cancel_management;
    /** @var int */
    public $rectification_management;
    /** @var int */
    public $convention_application;
    /** @var int */
    public $systematic_application;
    /** @var string */
    public $convention_application_date;
    /** @var int */
    public $group_id;
    /** @var int */
    public $jfse_id;

    public function getProps(): array
    {
        $props                                = parent::getProps();
        $props["convention_id"]               = 'num';
        $props["signer_organization_number"]  = 'str';
        $props["signer"]                      = 'str';
        $props["convention_type"]             = 'str';
        $props["secondary_criteria"]          = 'str';
        $props["agreement_type"]              = 'str';
        $props["signer_organization_label"]   = 'str';
        $props["amc_number"]                  = 'str';
        $props["amc_label"]                   = 'str';
        $props["statutory_operator"]          = 'str';
        $props["routing_code"]                = 'str';
        $props["host_id"]                     = 'str';
        $props["domain_name"]                 = 'str';
        $props["sts_referral_code"]           = 'str';
        $props["group_convention_flag"]       = 'num default|0';
        $props["certificate_use_flag"]        = 'num default|0';
        $props["sts_disabled_flag"]           = 'num default|0';
        $props["cancel_management"]           = 'num default|0';
        $props["rectification_management"]    = 'num default|0';
        $props["convention_application"]      = 'num default|0';
        $props["systematic_application"]      = 'num default|0';
        $props["convention_application_date"] = 'str';
        $props["group_id"]                    = 'num';
        $props["jfse_id"]                     = 'num';

        return $props;
    }
}
