{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=anticipated_directives ajax=$ajax}}
{{mb_script module=patients script=correspondant ajax=$ajax}}

<script>
  Main.add(function () {
    AnticipatedDirectives.addHolder();
  });
</script>

<form name="edit_directive_anticipee" method="post">
  {{mb_key object=$directive_anticipee}}
  {{mb_class object=$directive_anticipee}}
  <input type="hidden" name="del" value="" />
  <input type="hidden" name="patient_id" value="{{$patient->_id}}" />
  <input type="hidden" name="detenteur_class" value="{{$directive_anticipee->detenteur_class}}" />
  <input type="hidden" name="detenteur_id" value="{{$directive_anticipee->detenteur_id}}" />

  <fieldset>
    <legend>{{tr}}CDirectiveAnticipee-Adding a directive{{/tr}}</legend>

    <table class="main form">
      <tr>
        <th>{{mb_label object=$directive_anticipee field=date_recueil}}</th>
        <td>{{mb_field object=$directive_anticipee field=date_recueil register=true form="edit_directive_anticipee"}}</td>
      </tr>

      <tr>
        <th>{{mb_label object=$directive_anticipee field=date_validite}}</th>
        <td>{{mb_field object=$directive_anticipee field=date_validite register=true form="edit_directive_anticipee"}}</td>
      </tr>

      <tr>
        <th>{{mb_label object=$directive_anticipee field=description}}</th>
        <td>{{mb_field object=$directive_anticipee field=description rows="4" form="edit_directive_anticipee"
          aidesaisie="validateOnBlur: 0"}}</td>
      </tr>

      <tr>
        <th>{{mb_label object=$directive_anticipee field=detenteur_id}}</th>
        <td>
          <select name="select_detenteur_id" class="notNull" data-patient-id="{{$patient->_id}}">
            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
            <optgroup label="{{tr}}CPatient{{/tr}}">
              <option value="{{$patient->_guid}}" {{if $directive_anticipee->detenteur_id == $patient->_id}}selected{{/if}}>
                  {{$patient->_view}}
              </option>
            </optgroup>
            <optgroup id="patients_correspondants" label="{{tr}}CMedecin-back-patients_correspondants{{/tr}}">
              {{if $patient->_ref_correspondants_patient|@count}}
                {{foreach from=$patient->_ref_correspondants_patient item=_correspondant}}
                  <option value="{{$_correspondant->_guid}}" {{if $directive_anticipee->detenteur_id == $_correspondant->_id}}selected{{/if}}>
                      {{$_correspondant->_longview}}
                  </option>
                {{/foreach}}
              {{/if}}
            </optgroup>

            {{if $correspondants|@count}}
              <optgroup id="doctors_correspondants" label="{{tr}}CFunctions-back-medecins_function{{/tr}}">
                {{foreach from=$correspondants item=_medecin_correspondant}}
                  <option value="{{$_medecin_correspondant->_guid}}" {{if $directive_anticipee->detenteur_id == $_medecin_correspondant->medecin_id}}selected{{/if}}>
                      {{$_medecin_correspondant->_view}}
                  </option>
                {{/foreach}}
              </optgroup>
            {{/if}}
          </select>
          <button type="button" class="add notext add-holder" data-patient-id="{{$patient->_id}}"></button>
        </td>
      </tr>

      <tr>
        <td class="button" colspan="2">
          {{if !$directive_anticipee->_id}}
            <button type="button" class="save" onclick="AnticipatedDirectives.submitDirective(this.form);">{{tr}}Save{{/tr}}</button>
          {{else}}
            <button type="button" class="edit" onclick="AnticipatedDirectives.submitDirective(this.form);">{{tr}}Edit{{/tr}}</button>
            <button type="button" class="trash"
                    onclick="AnticipatedDirectives.deleteDirective(this.form, '{{$directive_anticipee->_view|smarty:nodefaults|JSAttribute}}')">
              {{tr}}Delete{{/tr}}
            </button>
          {{/if}}
        </td>
      </tr>
    </table>
  </fieldset>
</form>
