{{*
 * @package Mediboard\dPpatients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=type_evenement_patient ajax=1}}

<script>
  emptyFields = function () {
    var form = getForm("editEvenement");
    var fields = ["date", "date_da", "libelle", "description", "type_evenement_patient_id"];
    fields.each(function (field) {
      $V(form.elements[field], "");
    });
  };

  checkRappel = function (checkbox) {
    if ($$('select[name=type_evenement_patient_id]')[0].selectedOptions[0].dataset.mailingModelId) {
      $V(checkbox.form.rappel, 1, false);
      $('rappel-writable').hide();
      $('rappel-readonly').show();
    }
    else {
      $('rappel-writable').show();
      $('rappel-readonly').hide();
    }

    var field = checkbox.form.elements['type_evenement_patient_id']
    if ($V(checkbox) == '1') {
      field.addClassName('notNull');
      if (field.getLabel()) {
        field.getLabel().addClassName('notNull');
      }
      field.observe('change', notNullOK).observe('keyup', notNullOK).observe('ui:change', notNullOK);
      field.fire('ui:change');
      $('notification_infos').show();
    } else {
      field.removeClassName('notNull');
      if (field.getLabel()) {
        field.getLabel().removeClassName('notNull').removeClassName('notNullOK');
      }
      field.stopObserving('change', notNullOK).stopObserving('keyup', notNullOK).stopObserving('ui:change', notNullOK);
      $('notification_infos').hide();
    }
  };

  {{if $evenement_patient->_id}}
  Main.add(function () {
    checkRappel(getForm('editEvenement').elements['rappel']);
    EvtPatient.showNotificationInfos('{{$evenement_patient->type_evenement_patient_id}}');
  });
  {{/if}}
</script>

<form name="delEvenement{{$evenement_patient->_id}}" method="post">
  {{mb_class class=CEvenementPatient}}
  {{mb_key object=$evenement_patient}}
</form>

{{if !$inner_content}}
<table class="main layout">
  <tr>
    <td id="edit_evenements_patient_container">
      {{/if}}
      <table class="main">
        <tr>
          <td class="halfPane">
            <fieldset>
              <legend>
                {{if !$evenement_patient->_id}}
                  {{tr}}CEvenementPatient-Adding of the patient event{{/tr}}
                {{else}}
                  {{tr}}CEvenementPatient-Modifying of the patient event{{/tr}}
                {{/if}}
              </legend>
              <form name="editEvenement" method="post" onsubmit="return EvtPatient.onSubmitEvenement(this);">
                <input type="hidden" name="m" value="patients" />
                <input type="hidden" name="dosql" value="do_evenement_patient_aed" />
                {{mb_key object=$evenement_patient}}
                <input type="hidden" name="del" value="0" />
                <input type="hidden" name="_patient_id" value="{{$patient->_id}}" />

                <table class="form me-no-box-shadow">
                  <tr>
                    {{me_form_field nb_cells=2 mb_object=$evenement_patient mb_field="date"}}
                      {{mb_field object=$evenement_patient field=date form=editEvenement register=$register_date}}
                    {{/me_form_field}}
                  </tr>
                  <tr>
                    {{me_form_field nb_cells=2 mb_object=$evenement_patient mb_field="libelle"}}
                      {{mb_field object=$evenement_patient field=libelle style="width: 15em;"}}
                    {{/me_form_field}}
                  </tr>
                  <tr>
                    {{me_form_field nb_cells=2 mb_object=$evenement_patient mb_field="praticien_id"}}
                      <select name="praticien_id" style="width: 15em;"
                              {{if ($evenement_patient->_count_actes || $evenement_patient->valide)}}disabled{{/if}}>
                        <option value="">&mdash; {{tr}}CMediusers-select-praticien{{/tr}}</option>
                        {{mb_include module=mediusers template=inc_options_mediuser selected=$evenement_patient->praticien_id list=$praticiens}}
                      </select>
                    {{/me_form_field}}
                  </tr>
                  <tr>
                    {{me_form_field nb_cells=2 mb_object=$evenement_patient mb_field="type_evenement_patient_id" id="refresh_list_type"}}
                      {{mb_include module=patients template=inc_list_type_evenements_patient type_evenement_id=$evenement_patient->type_evenement_patient_id types=$types}}
                    {{/me_form_field}}
                  </tr>
                  <tr id="rappel-writable">
                    {{me_form_bool nb_cells=2 mb_object=$evenement_patient mb_field="rappel"}}
                      {{mb_field object=$evenement_patient field=rappel typeEnum=checkbox onchange="checkRappel(this);"}}
                    {{/me_form_bool}}
                  </tr>
                  <tr id="rappel-readonly" style="display: none;"><td colspan="2"><input type="checkbox" checked disabled> {{tr}}CEvenementPatient-rappel{{/tr}}</td></tr>
                  <tr>
                    <th class="me-padding-0"></th>
                    <td>
                      <span id="notification_infos" style="display: none;"></span>
                    </td>
                  </tr>
                  <tr>
                    {{me_form_field nb_cells=2 mb_object=$evenement_patient mb_field="description"}}
                      {{mb_field object=$evenement_patient field=description form=editEvenement}}
                    {{/me_form_field}}
                  </tr>

                  {{if !$is_sih_event}}
                    <tr>
                      {{me_form_bool nb_cells=2 mb_object=$evenement_patient mb_field="cancel"}}
                        {{mb_field object=$evenement_patient field=cancel form=editEvenement typeEnum="checkbox"}}
                      {{/me_form_bool}}
                    </tr>
                  {{/if}}

                  {{if $evenement_patient->alerter != 0}}
                    <tr>
                      <th>{{tr}}CEvenementAlerteUser{{/tr}}</th>
                      <td>
                        <ul>
                          {{foreach from=$evenement_patient->_ref_users item=mediuser}}
                            <li>{{mb_include module=mediusers template=inc_vw_mediuser}}</li>
                            {{foreachelse}}
                            <li class="empty">{{tr}}None{{/tr}}</li>
                          {{/foreach}}
                        </ul>
                      </td>
                    </tr>
                  {{/if}}

                   {{if "loinc"|module_active && $evenement_patient->_ref_codes_loinc}}
                     <tr>
                       <th>{{tr}}CLoinc-Loinc Codes{{/tr}}</th>
                       <td>
                            {{foreach from=$evenement_patient->_ref_codes_loinc item=_code name=count_code}}
                          <span onmouseover="ObjectTooltip.createEx(this, '{{$_code->_guid}}');">{{$_code->code}}</span>
                            {{if !$smarty.foreach.count_code.last}},{{/if}}
                          {{/foreach}}
                        </td>
                      </tr>
                    {{/if}}

                    {{if "snomed"|module_active && $evenement_patient->_ref_codes_snomed}}
                      <tr>
                        <th>{{tr}}CSnomed-Snomed Codes{{/tr}}</th>
                        <td>
                            {{foreach from=$evenement_patient->_ref_codes_snomed item=_code name=count_code}}
                          <span onmouseover="ObjectTooltip.createEx(this, '{{$_code->_guid}}');">{{$_code->code}}</span>
                            {{if !$smarty.foreach.count_code.last}},{{/if}}
                          {{/foreach}}
                        </td>
                      </tr>
                    {{/if}}
                  <tr>
                    <td colspan="3" class="button">
                      <button type="button" class="tick me-primary" onclick="this.form.onsubmit();">
                        {{if !$evenement_patient->_id}}
                          {{tr}}CEvenementPatient-action-Add event{{/tr}}
                        {{else}}
                          {{tr}}CEvenementPatient-action-Modify event{{/tr}}
                        {{/if}}
                      </button>
                      {{if $evenement_patient->_id}}
                        <button type="button" class="trash me-tertiary"
                                onclick="confirmDeletion(this.form, {typeName: 'l\'evenement', objName: '{{$evenement_patient->libelle|smarty:nodefaults|JSAttribute}}'},
                                  EvtPatient.onSubmitEvenement.curry(this.form))">{{tr}}Delete{{/tr}}</button>

                         {{if "loinc"|module_active || "snomed"|module_active}}
                          <button class="me-tertiary me-dark" type="button" title="{{tr}}CEvenementPatient-Nomenclature|pl-desc{{/tr}}" onclick="EvtPatient.showNomenclatures('{{$evenement_patient->_guid}}');">
                            <i class="far fa-eye"></i> {{tr}}CEvenementPatient-Nomenclature|pl{{/tr}}
                          </button>
                        {{/if}}
                      {{/if}}
                    </td>
                  </tr>
                </table>
              </form>
            </fieldset>
          </td>
        </tr>
      </table>
      {{if !$inner_content}}
    </td>
  </tr>
</table>
{{/if}}
