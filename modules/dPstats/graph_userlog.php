<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\System\CUserAction;
use Ox\Mediboard\System\CUserLog;

/**
 * User log by user stats
 * (Create, Update / Delete)
 *
 * @param string $to           Datetime where the search ends
 * @param string $interval     Type of interval (day, week, 8 weeks, year, 4 years, 20 years)
 * @param int    $user_id      User ID to filter
 * @param string $type         User log type to filter
 * @param string $object_class Class to filter
 * @param int    $object_id    Object ID to filter
 *
 * @return array
 */
function graphUserLog($to, $interval, $user_id = null, $type = null, $object_class = null, $object_id = null)
{
    switch ($interval) {
        default:
        case "one-week":
            $from_format   = "-1 WEEK";
            $step          = "+1 HOUR";
            $period_format = "%d/%m %Hh";
            $ticks_modulo  = 3;
            $by_locale     = "common-by-hour";
            break;
        case "eight-weeks":
            $from_format   = "-8 WEEK";
            $step          = "+1 DAY";
            $period_format = "%d/%m";
            $ticks_modulo  = 1;
            $by_locale     = "common-by-day";
            break;
        case "one-year":
            $from_format   = "-1 YEAR";
            $step          = "+1 WEEK";
            $period_format = "%Y S%U";
            $ticks_modulo  = 1;
            $by_locale     = "common-by-week";
            break;
        case "four-years":
            $from_format   = "-4 YEARS";
            $step          = "+1 MONTH";
            $period_format = "%m/%Y";
            $ticks_modulo  = 1;
            $by_locale     = "common-by-month";
            break;
    }

    $from  = CMbDT::dateTime($from_format, $to);
    $datax = [];
    $i     = 0;
    for ($d = $from; $d <= $to; $d = CMbDT::dateTime($step, $d)) {
        $period         = CMbDT::format($d, $period_format);
        $datax[$period] = [$i, $i % $ticks_modulo ? "" : $period];
        $i++;
    }

    // Series data
    $hits = [];

    // Series initialisation
    foreach ($datax as $x) {
        $hits[$x[0]] = [$x[0], 0];
    }

    $counts = CUserLog::countPeriodAggregation($from, $to, $period_format, $user_id, $type, $object_class, $object_id);
    foreach ($counts as $_period => $_result) {
        $index           = $datax[$_period][0];
        $hits[$index][1] = $_result;
    }

    $counts = CUserAction::countPeriodAggregation(
        $from,
        $to,
        $period_format,
        $user_id,
        $type,
        $object_class,
        $object_id
    );
    foreach ($counts as $_period => $_result) {
        $index           = $datax[$_period][0];
        $hits[$index][1] = $hits[$index][1] + $_result;
    }

    $datax = array_values($datax);

    $title = CAppUI::tr("CUserLog-title-stats");

    $user     = CUser::get($user_id);
    $subtitle = $user_id ?
        CAppUI::tr("CUserLog-user_id") . " : " . $user->_view :
        CAppUI::tr("CUser.all");
    $subtitle .= " - " . CAppUI::tr($by_locale);

    if ($type) {
        $subtitle .= " - " . CAppUI::tr("CUserLog-type") . " : " . CAppUI::tr("CUserLog.type.$type");
    }

    if ($object_class) {
        if ($object_id) {
            /** @var CStoredObject $object */
            $object = new $object_class;
            $object->load($object_id);
            $subtitle .= " - " . CAppUI::tr("CUserLog-object_id") . " : " . $object->_view;
        } else {
            $subtitle .= " - " . CAppUI::tr("CUserLog-object_class") . " : " . CAppUI::tr($object_class);
        }
    }

    $options = [
        "title"       => $title,
        "subtitle"    => $subtitle,
        "xaxis"       => [
            "labelsAngle" => 45,
            "ticks"       => $datax,
        ],
        "yaxis"       => [
            "min"             => 0,
            "title"           => "Actions",
            "autoscaleMargin" => 1,
        ],
        "grid"        => [
            "verticalLines" => false,
        ],
        "HtmlText"    => false,
        "spreadsheet" => [
            "show"             => true,
            "csvFileSeparator" => ";",
            "decimalSeparator" => ",",
        ],
    ];

    // Right axis (before in order the lines to be on top)
    $series[] = [
        "label" => "Actions utilisateur",
        "data"  => $hits,
        "bars"  => ["show" => true],
        "yaxis" => 1,
    ];

    return [
        "series"  => $series,
        "options" => $options,
    ];
}
