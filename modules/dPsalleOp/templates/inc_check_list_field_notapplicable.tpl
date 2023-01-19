{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<label style="white-space: nowrap;">
  <input type="radio" name="_items[{{$curr_type->_id}}]" value="na" {{if $curr_type->_checked == "na"}}checked="checked"{{/if}}
         onclick="EditDailyCheck.submitCheckList(this.form, true)" />
  {{tr}}CDailyCheckItem.checked.na{{/tr}}
</label>
