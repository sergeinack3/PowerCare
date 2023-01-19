{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="edit_inclusion_program" method="post"
      onsubmit="return onSubmitFormAjax(this, function() {
        Control.Modal.close();
      });">
  {{mb_key object=$inclusion_programme}}
  {{mb_class object=$inclusion_programme}}
  <input type="hidden" name="patient_id" value="{{$patient->_id}}" />

  <fieldset class="me-no-box-shadow">
    <legend>{{tr}}CInclusionProgramme-Adding patient to a program{{/tr}}</legend>

    <table class="main form me-no-box-shadow">
      <tr>
        <th>{{mb_label object=$inclusion_programme field=patient_id}}</th>
        <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">
            {{$patient->_view}}
          </span>
        </td>
      </tr>

      <tr>
        <th>{{mb_label class=$inclusion_programme field=programme_clinique_id}}</th>
        <td>
          <select name="programme_clinique_id" class="notNull"
                  onchange="$V(getForm('edit_inclusion_program').programme_clinique_id, this.value);">
            <option value="">&mdash; {{tr}}common-action-Choose{{/tr}}</option>
            {{foreach from=$programmes item=_programme}}
              <option value="{{$_programme->_id}}"
                      {{if $inclusion_programme->programme_clinique_id == $_programme->_id}}selected{{/if}}>{{$_programme->nom}}</option>
            {{/foreach}}
          </select>
        </td>
      </tr>

      <tr>
        <th>{{mb_label object=$inclusion_programme field=date_debut}}</th>
        <td>{{mb_field object=$inclusion_programme field=date_debut form="edit_inclusion_program" register=true}}</td>
      </tr>

      <tr>
        <th>{{mb_label object=$inclusion_programme field=date_fin}}</th>
        <td>{{mb_field object=$inclusion_programme field=date_fin form="edit_inclusion_program" register=true}}</td>
      </tr>

      <tr>
        <th>{{mb_label object=$inclusion_programme field=commentaire}}</th>
        <td>{{mb_field object=$inclusion_programme field=commentaire}}</td>
      </tr>

      <tr>
        <td class="button" colspan="2">
          {{if !$inclusion_programme->_id}}
            <button type="submit" class="save">{{tr}}Save{{/tr}}</button>
          {{else}}
            <button type="submit" class="save">{{tr}}Edit{{/tr}}</button>
            <button type="button" class="trash" onclick="confirmDeletion(this.form,{ajax:true}, {onComplete: Control.Modal.close})">
              {{tr}}Delete{{/tr}}
            </button>
          {{/if}}
        </td>
      </tr>
    </table>
  </fieldset>
</form>
