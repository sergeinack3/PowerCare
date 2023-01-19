{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="chgMode" action="?m={{$m}}" method="get">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="tab" value="{{$tab}}" />
  <select name="mode" onchange="this.form.submit()" style="width:135px">
    <option value="0" {{if $mode == 0}}selected="selected"{{/if}}>{{tr}}Instant view{{/tr}}</option>
    <option value="1" {{if $mode == 1}}selected="selected"{{/if}}>{{tr}}Day view{{/tr}}</option>
  </select>
</form>