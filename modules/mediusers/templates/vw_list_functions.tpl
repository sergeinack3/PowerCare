{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    $("listFunctionsDiv").fixedTableHeaders();
  });
</script>

<div id="listFunctionsDiv">
  <table class="tbl">
    <tbody>
      {{foreach from=$functions item=_function}}
      <tr class="{{if $_function->_id == $function_id}}selected{{/if}} {{if !$_function->actif}}hatching{{/if}}">
        <td style="width: 1%" class="compact">
          <button class="edit notext" onclick="CFunctions.editFunction('{{$_function->_id}}', this)"></button>
        </td>

        <td class="text" style="width: 50%">
          <span onmouseover="ObjectTooltip.createEx(this,'{{$_function->_guid}}')" class="mediuser" style="border-left-color: #{{$_function->color}};">
            {{$_function->text}}
          </span>
        </td>

        <td style="width: 20%">
          {{tr}}CFunctions.type.{{$_function->type}}{{/tr}}
        </td>

        <td style="width: 7%; text-align: center;">
          {{$_function->_count.users|nozero}}
        </td>

        <td style="width: 7%; text-align: center;">
          {{$_function->_count.secondary_functions|nozero}}
        </td>
      </tr>
      {{/foreach}}
    </tbody>
    <thead>
    <tr>
      <th></th>
      <th>{{mb_colonne class="CFunctions" field="text" order_col=$order_col order_way=$order_way function="CFunctions.changeFilter"}}</th>
      <th>{{mb_colonne class="CFunctions" field="type" order_col=$order_col order_way=$order_way function="CFunctions.changeFilter"}}</th>
      <th colspan="2">{{tr}}CFunctions-back-users{{/tr}}</th>
    </tr>
    <tr>
      <th colspan="3"></th>
      <th>Principaux</th>
      <th>Secondaires</th>
    </tr>
    </thead>
  </table>
</div>