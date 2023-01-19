<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Datatypes\Base;

use DateTime;
use DateTimeZone;
use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;

/**
 * A quantity specifying a point on the axis of natural time.
 * A point in time is most often represented as a calendar
 * expression.
 */
class CCDATS extends CCDAQTY
{

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    function getProps()
    {
        $props          = parent::getProps();
        $props["value"] = "CCDA_base_ts xml|attribute";

        return $props;
    }

    /**
     * Fcontion qui permet de tester si la classe fonctionne
     *
     * @return array
     */
    function test()
    {
        $tabTest = parent::test();

        /**
         * Test avec une valeur incorrecte
         */

        $this->setValue("TESTTEST");
        $tabTest[] = $this->sample("Test avec une valeur correcte", "Document invalide");

        /*-------------------------------------------------------------------------------------*/

        /**
         * Test avec une valeur correcte
         */

        $this->setValue("24141331462095.812975314545697850652375076363185459409261232419230495159675586");
        $tabTest[] = $this->sample("Test avec une valeur correcte", "Document valide");

        /*-------------------------------------------------------------------------------------*/

        return $tabTest;
    }

    /**
     * Setter value
     *
     * @param String $value String
     *
     * @return void
     */
    public function setValue($value)
    {
        if (!$value) {
            $this->value = null;

            return;
        }
        // format when value is progressive date
        $value = $this->format($value);
        $ts    = new CCDA_base_ts();
        $ts->setData($value);
        $this->value = $ts;
    }

    /**
     * Format progressive date
     *
     * @param string $datetime date
     *
     * @return string|null
     */
    private function format(string $datetime): ?string
    {
        if ($datetime == 'now') {
            $datetime = CMbDT::dateTime();
        }

        $explode = explode(' ', $datetime);
        $date    = $explode[0];
        $time    = $explode[1] ?? '';

        if (!str_contains($date, '-')) {
            throw new Exception('change date call');
        }

        [$year, $month, $day] = explode("-", $date);

        // format for progressive date
        if ($month == "00") {
            $month = "01";
        }
        if ($day == "00") {
            $day = "01";
        }

        $datetime = $time ? "$year$month$day $time" : "$year$month$day";
        $format   = $time ? 'YmdHisO' : 'Ymd';

        $timezone = new DateTimeZone(CAppUI::conf("timezone"));
        $date     = new DateTime($datetime, $timezone);

        return $date->format($format);
    }
}
