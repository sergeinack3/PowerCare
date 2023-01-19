{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include template=inc_pref spec=enum var=defaultRPUSort values="ccmu|_patient_id|_entree"}}
{{mb_include template=inc_pref spec=enum var=chooseSortRPU values="ASC|DESC"}}
{{mb_include template=inc_pref spec=bool var=showMissingRPU}}
