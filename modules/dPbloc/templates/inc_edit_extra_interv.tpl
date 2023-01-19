{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(SalleOp.reloadPersonnel.curry('{{$operation->_id}}'));
</script>

<table class="form">
  <tr>
    <th class="title" colspan="2">Changement de salle</th>
  </tr>
  <tr>
    <th>{{mb_label object=$operation field=salle_id}}</th>
    <td>
      <form name="editOp" method="post" onsubmit="return onSubmitFormAjax(this)">
        <input type="hidden" name="m" value="planningOp" />
        <input type="hidden" name="dosql" value="do_planning_aed" />
        <input type="hidden" name="ajax" value="1" />
        {{mb_key object=$operation}}
        <select name="salle_id" onchange="this.form.onsubmit()">
          {{foreach from=$blocs item=curr_bloc}}
            <optgroup label="{{$curr_bloc->nom}}">
              {{foreach from=$curr_bloc->_ref_salles item=curr_salle}}
                <option value="{{$curr_salle->_id}}" {{if $curr_salle->_id == $operation->salle_id}}selected="selected"{{/if}}>
                  {{$curr_salle->nom}}
                </option>
                {{foreachelse}}
                <option value="" disabled>{{tr}}CSalle.none{{/tr}}</option>
              {{/foreach}}
            </optgroup>
          {{/foreach}}
        </select>
      </form>
    </td>
  </tr>
  <tr>
    <td colspan="2" id="listPersonnel"></td>
  </tr>
</table>