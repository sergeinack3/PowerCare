{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=edit_consultation ajax=$ajax}}
{{if "oxCabinet"|module_active}}
    {{mb_script module=oxCabinet script=consultation_tamm ajax=$ajax}}
{{/if}}

{{if $patient->_ref_consultations|@count}}
  <div class="small-warning">
    <strong>{{tr}}Consult_immediate.warning{{/tr}}</strong>
    <ul>
      {{foreach from=$patient->_ref_consultations item=_consult name=consults_patient}}
        <li>{{tr}}CConsultation-consult-on{{/tr}} {{mb_value object=$_consult field=_date}} à {{mb_value object=$_consult field=heure}} avec {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_consult->_ref_plageconsult->_ref_chir}}
      {{/foreach}}
    </ul>
  </div>
{{/if}}

<script>
  submitForm = function(form) {
    if (form._prat_id.options[form._prat_id.selectedIndex].get("activite") === "mixte") {
      if (confirm($T("CConsultation-ask_create_sejour_consult"))) {
        $V(form._create_sejour_activite_mixte, "1");
      } else {
        $V(form._create_sejour_activite_mixte, "0");
      }
    }
    {{if $callback}}
      return onSubmitFormAjax(form, {onComplete: Control.Modal.close});
    {{else}}
      if (checkForm(form)) {
        return form.submit();
      }
    {{/if}}
  };

  Main.add(function() {
    {{if "dPplanningOp CSejour required_uf_med"|gconf === 'obl'}}
      Consultation.uf_medicale_mandatory = true;
    {{/if}}
      {{if 'oxCabinet'|module_active}}
          ConsultTamm.showSelectFunctionConsultImmediate(getForm("addConsultImmediate"));
      {{/if}}
  });
</script>

<form name="addConsultImmediate" action="?" method="post" onsubmit="return submitForm(this);">
  <input type="hidden" name="m" value="cabinet" />
  <input type="hidden" name="dosql" value="do_consult_now" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="_create_sejour_activite_mixte" />
  {{mb_field object=$consult field="patient_id" hidden=true}}
  {{mb_field object=$consult field="_operation_id" hidden=true}}
  {{mb_field object=$consult field="grossesse_id" hidden=true}}

  {{* External entity fields *}}
  {{mb_field object=$consult field=date_creation_anterieure hidden=true}}
  {{mb_field object=$consult field=agent hidden=true}}

  <input type="hidden" name="callback" value="{{$callback}}" />

  <table class="form">
    <tr>
      <th colspan="2" class="title">{{tr}}CConsultation-action-Immediate{{/tr}}</th>
    </tr>
    <tr>
      <th style="width: 40%">{{mb_label object=$consult field="_datetime"}}</th>
      <td>
          {{if 'oxCabinet'|module_active}}
            {{assign var=onChange value="Consultation.checkByDateAndPrat(this.form, 'consult_exists');ConsultTamm.showSelectFunctionConsultImmediate(getForm('addConsultImmediate'))"}}
          {{else}}
            {{assign var=onChange value="Consultation.checkByDateAndPrat(this.form, 'consult_exists')"}}
          {{/if}}
          {{mb_field object=$consult field="_datetime" canNull=false register=true form=addConsultImmediate onchange="$onChange"}}
      </td>
    </tr>

    <tr>
      <th class="notNull">{{mb_label object=$consult field="_prat_id"}}</th>

      <td>
        <select name="_prat_id" class="notNull ref"
                onchange="Consultation.checkByDateAndPrat(this.form, 'consult_exists'); Consultation.toggleUfMedicaleField(this); {{if 'oxCabinet'|module_active}}ConsultTamm.showSelectFunctionConsultImmediate(this.form){{/if}}">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>

          {{mb_include module=mediusers template=inc_options_mediuser selected=$app->user_id list=$praticiens}}
        </select>
      </td>
    </tr>

    {{if 'oxCabinet'|module_active}}
      <tr id="function">
      </tr>
    {{/if}}

    {{mb_include module=cabinet template=inc_ufs_charge_price}}

    <tr>
      <td colspan="2" class="button">
        <div id="consult_exists">
          {{mb_include module=cabinet template=inc_consult_immediate_validation}}
        </div>
      </td>
    </tr>
  </table>
</form>
