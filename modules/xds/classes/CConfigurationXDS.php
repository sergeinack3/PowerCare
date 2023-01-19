<?php
/**
 * @package Mediboard\Dmi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Xds;

use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

class CConfigurationXDS extends AbstractConfigurationRegister {

    /**
     * @return void
     */
    public function register() {
        CConfiguration::register(
            array(
                "CGroups" => array(
                    "xds" => array(
                        "general" => array(
                            "use_siret_finess_ans"   => "enum list|siret|finess default|siret",
                            "use_siret_finess_xds"   => "enum list|siret|finess default|siret",
                            "use_siret_finess_zepra" => "enum list|siret|finess default|finess",
                        ),
                    )
                )
            )
        );
    }
}
