<?php

/**
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbObjectSpec;

/**
 * Description
 */
class CNGAP implements IShortNameAutoloadable
{
    /** @var CMbObjectSpec */
    static $spec;

    /**
     * Get object spec
     *
     * @return CMbObjectSpec
     */
    public static function getSpec(): CMbObjectSpec
    {
        if (self::$spec) {
            return self::$spec;
        }

        $spec      = new CMbObjectSpec();
        $spec->dsn = 'ccamV2';
        $spec->init();

        return self::$spec = $spec;
    }

    /**
     * Return the current version and the next available version of the NGAP database
     *
     * @return array (string current version, string next version)
     */
    public static function getDatabaseVersions(): array
    {
        return [
            "38387"       => [
                [
                    "table_name" => "tarif_ngap",
                    "filters"    => [
                        "code" => "= 'G'",
                    ],
                ],
            ],
            "39893"       => [
                [
                    "table_name" => "tarif_ngap",
                    "filters"    => [
                        "code"  => "= 'CNP'",
                        "tarif" => "= '39'",
                    ],
                ],
            ],
            "41279-41294" => [
                [
                    "table_name" => "tarif_ngap",
                    "filters"    => [
                        "code" => "= 'APC'",
                    ],
                ],
            ],
            "41942"       => [
                [
                    "table_name" => "tarif_ngap",
                    "filters"    => [
                        "code" => "= 'CCX'",
                    ],
                ],
            ],
            "42076"       => [
                [
                    "table_name" => "associations_ngap",
                    "filters"    => [
                        "code"         => "= 'MCX'",
                        "associations" => "LIKE '%C|%'",
                    ],
                ],
            ],
            "42640"       => [
                [
                    "table_name" => "codes_ngap",
                    "filters"    => [
                        "code" => "= 'MCU'",
                    ],
                ],
            ],
            "43241"       => [
                [
                    "table_name" => "tarif_ngap",
                    "filters"    => [
                        "tarif_ngap" => "= 'AMY'",
                        "coef_max"   => "= 30.50'",
                    ],
                ],
            ],
            "43490"       => [
                [
                    "table_name" => "tarif_ngap",
                    "filters"    => [
                        "tarif_ngap" => "= 'U45'",
                    ],
                ],
            ],
            "43596"       => [
                [
                    "table_name" => "tarif_ngap",
                    "filters"    => [
                        "tarif_ngap" => "= 'IC'",
                    ],
                ],
            ],
            "45331"       => [
                [
                    "table_name" => "specialite_to_tarif_ngap",
                    "filters"    => [
                        "tarif_id"   => "= 718",
                        "specialite" => "= 32",
                    ],
                ],
            ],
            "46181"       => [
                [
                    "table_name" => "specialite_to_tarif_ngap",
                    "filters"    => [
                        "tarif_id"   => "= 54",
                        "specialite" => "= 21",
                    ],
                ],
            ],
            "46863"       => [
                [
                    "table_name" => "tarif_ngap",
                    "filters"    => [
                        "code"             => "= 'APC'",
                        "complement_ferie" => "= 1",
                    ],
                ],
            ],
            "46925"       => [
                [
                    "table_name" => "specialite_to_tarif_ngap",
                    "filters"    => [
                        "tarif_ngap.code"                     => "= 'MA'",
                        "specialite_to_tarif_ngap.specialite" => "= 2",
                    ],
                    "ljoin"      => [
                        "tarif_ngap" => "tarif_ngap.`tarif_ngap_id` = specialite_to_tarif_ngap.`tarif_id`",
                    ],
                ],
            ],
            "46926"       => [
                [
                    "table_name" => "codes_ngap",
                    "filters"    => [
                        "code" => "= 'MSF'",
                    ],
                ],
            ],
            "46927"       => [
                [
                    "table_name" => "codes_ngap",
                    "filters"    => [
                        "code" => "= 'AKI'",
                    ],
                ],
            ],
            "46928"       => [
                [
                    "table_name" => "codes_ngap",
                    "filters"    => [
                        "code" => "= 'ASE'",
                    ],
                ],
            ],
        ];
    }
}
