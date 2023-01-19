{{*
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<label for="input_function">{{tr}}importTools-label-export-function{{/tr}}</label>
<select name="function_select" id="function_select">
  <option value="all" selected>-- Toutes</option>
  {{foreach from=$functions item=_function}}
    <option value="{{$_function->_id}}">
        {{$_function}}
    </option>
  {{/foreach}}
</select>
