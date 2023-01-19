<?php

/**
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\BloodSalvage\Services;

use Exception;
use Ox\Core\CMbDT;
use Ox\Mediboard\BloodSalvage\CBloodSalvage;
use Ox\Mediboard\Personnel\CAffectationPersonnel;

/**
 * Helpers functions
 */
class BloodSalvageService
{
    public static function loadAffected(
        int &$blood_salvage_id,
        array &$list_nurse_sspi,
        array &$tabAffected,
        array &$timingAffect
    ): void {
        $affectation               = new CAffectationPersonnel();
        $affectation->object_class = "CBloodSalvage";
        $affectation->object_id    = $blood_salvage_id;
        $tabAffected               = $affectation->loadMatchingList();

        foreach ($tabAffected as $key => $affect) {
            if (array_key_exists($affect->personnel_id, $list_nurse_sspi)) {
                unset($list_nurse_sspi[$affect->personnel_id]);
            }
            $affect->loadRefPersonnel()->loadRefUser();
        }

        // Initialisations des tableaux des timings
        foreach ($tabAffected as $key => $affectation) {
            $timingAffect[$affectation->_id]["_debut"] = [];
            $timingAffect[$affectation->_id]["_fin"]   = [];
        }

        // Remplissage des tableaux des timings
        foreach ($tabAffected as $id => $affectation) {
            foreach ($timingAffect[$affectation->_id] as $key => $value) {
                for ($i = -10; $i < 10 && $affectation->$key !== null; $i++) {
                    $timingAffect[$affectation->_id][$key][] = CMbDT::time("$i minutes", $affectation->$key);
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    public static function fillData(array &$where, array $ljoin, array &$serie, array $dates): void
    {
        $d          = &$serie['data'];
        $bs         = new CBloodSalvage();
        $ds         = $bs->getDS();
        $keys_where = array_keys($where);
        $pos        = end($keys_where);
        $i          = 0;

        foreach ($dates as $month => $date) {
            $where['operations.date'] = $ds->prepareBetween($date['start'], $date['end']);
            $count                    = $bs->countList($where, 'patients.patient_id', $ljoin);
            $d[$i]                    = [$i, intval($count)];
            $i++;
        }
        unset($where[$pos]);
    }

    /**
     * @throws Exception
     */
    public static function computeMeanValue(
        array &$where,
        array &$ljoin,
        array &$serie,
        array $dates,
        string $prop
    ): void {
        $d          = &$serie['data'];
        $bs         = new CBloodSalvage();
        $ds         = $bs->getDS();
        $keys_where = array_keys($where);
        $pos        = end($keys_where);
        $i          = 0;

        foreach ($dates as $date) {
            $where['operations.date'] = $ds->prepareBetween($date['start'], $date['end']);
            $list                     = $bs->loadList($where, null, null, null, $ljoin);

            $total = 0;
            foreach ($list as $_bs) {
                $total += $_bs->$prop;
            }
            $count = count($list);
            $mean  = $count ? $total / $count : 0;
            $d[$i] = [$i, $mean];
            $i++;
        }
        unset($where[$pos]);
    }
}
