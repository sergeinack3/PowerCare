{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(function() {
    Blocage.refreshPlageToDelete(getForm('editBlocage'));
  });
</script>

<form name="editBlocage" method="post" onsubmit="onSubmitFormAjax(this)">
  <input type="hidden" name="m" value="dPbloc" />
  <input type="hidden" name="dosql" value="do_blocage_aed" />
  <input type="hidden" name="callback" value="Blocage.afterEditBlocage">
  <input type="hidden" name="del" value="0">
  {{mb_key object=$blocage}}
  
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$blocage}}
    <tr>
      <th>
        {{mb_label object=$blocage field=salle_id}}
      </th>
      <td>
        <select name="salle_id" class="notNull" onchange="Blocage.refreshPlageToDelete(this.form)">
          <option value="">&mdash; Choisissez une salle</option>
          {{foreach from=$blocs item=_bloc}}
            <optgroup label="{{$_bloc}}">
              {{foreach from=$_bloc->_ref_salles item=_salle}}
                <option value="{{$_salle->_id}}" {{if $_salle->_id == $blocage->salle_id}}selected="selected"{{/if}}>
                  {{$_salle->nom}}
                </option>
              {{/foreach}}
            </optgroup>
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$blocage field=libelle}}
      </th>
      <td>
        {{mb_field object=$blocage field=libelle}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$blocage field=deb}}
      </th>
      <td>
        {{mb_field object=$blocage field=deb form=editBlocage register=true onchange="Blocage.refreshPlageToDelete(this.form)"}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$blocage field=fin}}
      </th>
      <td>
        {{mb_field object=$blocage field=fin form=editBlocage register=true onchange="Blocage.refreshPlageToDelete(this.form)"}}
      </td>
    </tr>
    <tr>
      <td id="plages_deleted" colspan="2">
      
      </td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        {{if $blocage->_id}}
          <button type="button" class="save" onclick="this.form.onsubmit()">{{tr}}Save{{/tr}}</button>
          <button type="button" class="trash" onclick="confirmDeletion(this.form, {objName:'{{$blocage}}', ajax: 1})">{{tr}}Delete{{/tr}}</button>
        {{else}}
          <button type="button" class="save" onclick="this.form.onsubmit()">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
