{{*
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(function () {
    Calendar.regField(getForm("selectSalle").date, null, {noView: true});
  });
</script>

<form action="?" name="selectSalle" method="get">

  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="op" value="0" />

  <table class="form">
    <tr>
      <th class="category" colspan="2">
        {{$date|date_format:$conf.longdate}}
        <input type="hidden" name="date" class="date" value="{{$date}}" onchange="this.form.submit()" />
      </th>
    </tr>

    <tr>
      <th><label for="salle" title="Salle d'opération">Salle</label></th>
      <td>
        <select name="salle" onchange="this.form.submit()">
          <option value="">&mdash; {{tr}}CSalle.none{{/tr}}</option>
          {{foreach from=$listBlocs item=curr_bloc}}
            <optgroup label="{{$curr_bloc->nom}}">
              {{foreach from=$curr_bloc->_ref_salles item=curr_salle}}
                <option value="{{$curr_salle->_id}}" {{if $curr_salle->_id == $salle->_id}}selected="selected"{{/if}}
                  {{if !$curr_salle->actif}}style="display: none;"{{/if}}>
                  {{$curr_salle->nom}}
                </option>
                {{foreachelse}}
                <option value="" disabled="disabled">{{tr}}CSalle.none{{/tr}}</option>
              {{/foreach}}
            </optgroup>
          {{/foreach}}
        </select>
      </td>
    </tr>
  </table>

</form>
{{mb_include module=bloodSalvage template=inc_details_plages}}