{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=col value='appbar_shortcuts'}}
{{assign var=class value=$shortcut}}

<tr>
  <th class="category" colspan="10">{{tr}}{{$class}}{{/tr}}</th>
</tr>

<tr>
  <th style="width: 50%">
    <label for="{{$col}}[{{$class}}]" title="{{tr}}config-{{$col}}-{{$class}}-desc{{/tr}}">
      {{tr}}config-{{$col}}-{{$class}}{{/tr}}
    </label>  
  </th>
  <td>
    <select class="bool" name="{{$col}}[{{$class}}]">
      <option value="0" {{if 0 == @$conf.$col.$class}} selected="selected" {{/if}}>{{tr}}bool.0{{/tr}}</option>
      <option value="1" {{if 1 == @$conf.$col.$class}} selected="selected" {{/if}}>{{tr}}bool.1{{/tr}}</option>
    </select>
  </td>
</tr>
