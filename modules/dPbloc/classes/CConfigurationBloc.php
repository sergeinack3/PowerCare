<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Bloc;

use Ox\Mediboard\System\AbstractConfigurationRegister;
use Ox\Mediboard\System\CConfiguration;

/**
 * @codeCoverageIgnore
 */
class CConfigurationBloc extends AbstractConfigurationRegister
{

    /**
     * @return mixed
     */
    public function register()
    {
        $listHours = "";
        foreach (range(0, 23) as $_hour) {
            $hour = str_pad($_hour, 2, "0", STR_PAD_LEFT);
            if ($listHours) {
                $listHours .= "|";
            }
            $listHours .= $hour;
        }

        CConfiguration::register(
            [
                "CGroups" => [
                    "dPbloc" => [
                        "CPlageOp"          => [
                            "original_owner"        => "bool default|0",
                            "locked"                => "bool default|1",
                            "hours_start"           => "enum list|$listHours default|8",
                            "hours_stop"            => "enum list|$listHours default|20",
                            "minutes_interval"      => "num default|15",
                            "systeme_materiel"      => "enum list|standard|expert default|standard localize",
                            "view_empty_plage_op"   => 'bool default|1',
                            "color_free"            => "color default|fff",
                            "color_deleted"         => "color default|fff",
                            "fill_rate_empty"       => "color default|8a88ff",
                            "fill_rate_normal"      => "color default|99ff99",
                            "fill_rate_booked"      => "color default|ffe188",
                            "fill_rate_full"        => "color default|ff8b8b",
                            'reorder_real_duration' => 'bool default|0',
                        ],
                        "mode_presentation" => [
                            "salles_count"   => "num default|4",
                            "refresh_period" => "num default|30",
                        ],
                        "affichage"         => [
                            "chambre_operation"   => "bool default|0",
                            "view_prepost_suivi"  => "bool default|0",
                            "time_autorefresh"    => "enum list|120|300|600 default|120 localize",
                            "view_tools"          => "bool default|0",
                            "view_required_tools" => "bool default|0",
                            "view_rques"          => "bool default|0",
                            "view_anesth_type"    => "bool default|0",
                        ],
                        "printing"          => [
                            "format_print"          => "enum localize list|standard|advanced|advanced_2 default|standard",
                            "hour_midi_fullprint"   => "enum list|$listHours default|12",
                            "plage_vide"            => "bool default|0",
                            "libelle_ccam"          => "bool default|1",
                            "view_materiel"         => "bool default|1",
                            "view_missing_materiel" => "bool default|1",
                            "view_extra"            => "bool default|1",
                            "view_duree"            => "bool default|1",
                            "view_hors_plage"       => "bool default|1",
                            "view_convalescence"    => "bool default|0",
                            "show_comment_sejour"   => "bool default|1",
                            "show_anesth_alerts"    => "bool default|1",
                        ],
                        "printing_standard" => [
                            "col1" => "enum list|interv|sejour|patient default|interv localize",
                            "col2" => "enum list|interv|sejour|patient default|sejour localize",
                            "col3" => "enum list|interv|sejour|patient default|patient localize",
                        ],
                        "other"             => [
                            "refresh_period_suivi_bloc" => "num default|90",
                            "vignette_anonyme"          => "bool default|0",
                            'operation_shift_value'     => 'num default|10',
                        ],
                    ],
                ],
            ]
        );
    }
}