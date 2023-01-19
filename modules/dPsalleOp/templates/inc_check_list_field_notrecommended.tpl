{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<label style="white-space: nowrap;">
	<input type="radio" name="_items[{{$curr_type->_id}}]" value="nr" {{if $curr_type->_checked == "nr"}}checked="checked"{{/if}}
         onclick="EditDailyCheck.submitCheckList(this.form, true)" />
	{{tr}}CDailyCheckItem.checked.nr{{/tr}}
</label>