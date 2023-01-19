<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\ViewModels\Invoicing;

use Ox\Mediboard\Jfse\ViewModels\CJfseViewModel;

class CJfseInvoiceUserInterface extends CJfseViewModel
{
    /** @var bool */
    public $proof_amo;

    /** @var bool */
    public $alsace_moselle;

    /** @var bool */
    public $beneficiary;

    /** @var bool */
    public $prescriber;

    /** @var bool */
    public $ame;

    /** @var bool */
    public $maternity_exoneration;

    /** @var bool */
    public $sncf;

    /** @var bool */
    public $amc_third_party_payment;

    /** @var bool */
    public $pharmacy;

    /** @var bool */
    public $care_path;

    /** @var bool */
    public $ccam_acts;

    /** @var bool */
    public $medical_acts;

    /** @var bool */
    public $cnda_mode;

    /** @var bool */
    public $acts_lock;

    /** @var bool */
    public $amendment_27_consultation_help;

    /** @var bool */
    public $amendment_27_referring_physician;

    /** @var bool */
    public $amendment_27_enforceable_tariff;

    /** @var bool */
    public $anonymize;

    /** @var bool */
    public $display_treatment_type = '0';


    public function getProps(): array
    {
        $props = parent::getProps();

        $props['proof_amo']                        = 'bool';
        $props['alsace_moselle']                   = 'bool';
        $props['beneficiary']                      = 'bool';
        $props['prescriber']                       = 'bool';
        $props['ame']                              = 'bool';
        $props['maternity_exoneration']            = 'bool';
        $props['sncf']                             = 'bool';
        $props['amc_third_party_payment']          = 'bool';
        $props['pharmacy']                         = 'bool';
        $props['care_path']                        = 'bool';
        $props['ccam_acts']                        = 'bool';
        $props['medical_acts']                     = 'bool';
        $props['cnda_mode']                        = 'bool';
        $props['acts_lock']                        = 'bool';
        $props['amendment_27_consultation_help']   = 'bool';
        $props['amendment_27_referring_physician'] = 'bool';
        $props['amendment_27_enforceable_tariff']  = 'bool';
        $props['anonymize']                        = 'bool';
        $props['display_treatment_type']           = 'bool';

        return $props;
    }

}
