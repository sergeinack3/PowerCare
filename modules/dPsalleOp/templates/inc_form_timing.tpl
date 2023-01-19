{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="timing{{$selOp->_id}}-{{$field}}" method="post">
  <input type="hidden" name="m" value="planningOp"/>
  <input type="hidden" name="dosql" value="do_planning_aed"/>
  {{mb_key object=$selOp}}

  {{mb_include module=salleOp template=inc_field_timing object=$selOp field=$field form="timing`$selOp->_id`-`$field`"}}
</form>
