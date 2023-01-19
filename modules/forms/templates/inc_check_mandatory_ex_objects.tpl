{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $ex_events}}
  <i class="fa fa-exclamation-triangle" style="color: goldenrod;"></i>

  <button type="button" class="forms notext compact me-primary" onclick="ExObject.showMandatoryExClasses('{{$object->_class}}', '{{$object->_id}}');">
    {{tr}}CExClass-Mandatory object|pl{{/tr}}
  </button>

  {{mb_include module=system template=inc_vw_counter_tip count=$ex_events|@count}}
{{/if}}