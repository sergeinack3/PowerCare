{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $precisions|@count > 0}}
  <select name="geste_perop_precision_id" class="me-margin-0" onchange="GestePerop.showPrecisionValues(this.value);">
    <option value="">
        {{tr}}CGestePeropPrecision.none{{/tr}}
    </option>
      {{foreach from=$precisions item=_precision}}
        <option value="{{$_precision->_id}}"
                {{if $_precision->_id == $evenement->geste_perop_precision_id}} selected="selected"{{/if}}>
            {{$_precision->_view}}
        </option>
      {{/foreach}}
  </select>
{{else}}
  <span class="empty">{{tr}}CGestePeropPrecision.none{{/tr}}</span>
{{/if}}
