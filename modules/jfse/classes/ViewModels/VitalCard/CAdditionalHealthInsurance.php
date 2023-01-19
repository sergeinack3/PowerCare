<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\VitalCard;

use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

class CAdditionalHealthInsurance extends CJfseViewModel
{
    /** @var string */
    public $number_b2;

    /** @var string */
    public $number_edi;

    /** @var string */
    public $subscriber_number;

    /** @var string */
    public $treatment_indicator;

    /** @var string */
    public $routing_code;

    /** @var string */
    public $host_id;

    /** @var string */
    public $domain_name;

    /** @var string */
    public $services_type;

    /** @var string */
    public $associated_services_contract;

    /** @var string */
    public $referral_sts_code;

    /** @var string */
    public $begin_date;

    /** @var string */
    public $end_date;

    /** @var string */
    public $label;

    /** @var string */
    public $contract_type;

    /** @var string */
    public $pec;

    /** @var string */
    public $secondary_criteria;

    /** @var string */
    public $convention_type;

    /** @var bool */
    public $paper_mode;

    /** @var bool */
    public $rights_forcing;

    /** @var int */
    public $type;

    /** @var string */
    public $id;

    /** @var int */
    public $reference_date;

    public function getProps(): array
    {
        $props = parent::getProps();

        $props["number_b2"]                    = "str notNull";
        $props["number_edi"]                   = "str";
        $props["subscriber_number"]            = "str";
        $props["treatment_indicator"]          = "str";
        $props["routing_code"]                 = "str maxLength|2 minLength|2";
        $props["host_id"]                      = "str maxLength|3 minLength|3";
        $props["domain_name"]                  = "str";
        $props["services_type"]                = "str";
        $props["associated_services_contract"] = "str";
        $props["referral_sts_code"]            = "str";
        $props["label"]                        = "str";
        $props['begin_date']                   = 'date';
        $props['end_date']                     = 'date';
        $props['contract_type']                = 'str';
        $props['pec']                          = 'str';
        $props['secondary_criteria']           = 'str';
        $props['convention_type']              = 'str';
        $props['paper_mode']                   = 'bool';
        $props['rights_forcing']               = 'bool';
        $props['type']                         = 'enum list|0|1|2|3|4|5|6';
        $props['id']                           = 'str';
        $props['reference_date']               = 'enum list|0|1|2';

        return $props;
    }
}
