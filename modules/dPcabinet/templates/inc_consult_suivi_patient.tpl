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
<script>
  submitForm = function(form) {
    return onSubmitFormAjax(form, {onComplete: Control.Modal.close});
  };

  Main.add(function() {
      {{if 'oxCabinet'|module_active}}
    ConsultTamm.showSelectFunctionConsultImmediate(getForm("addConsultSuiviPatient"));
      {{/if}}
  });
</script>

<form name="addConsultSuiviPatient" action="?" method="post" onsubmit="return submitForm(this);">
  <input type="hidden" name="m" value="cabinet" />
  <input type="hidden" name="dosql" value="do_consult_now" />
  <input type="hidden" name="del" value="0" />

    {{mb_field object=$consult field="type_consultation" value="suivi_patient" hidden=true}}
    {{mb_field object=$consult field="patient_id" hidden=true}}
    {{mb_field object=$consult field="grossesse_id" hidden=true}}

    {{* External entity fields *}}
    {{mb_field object=$consult field=date_creation_anterieure hidden=true}}
    {{mb_field object=$consult field=agent hidden=true}}

  <input type="hidden" name="callback" value="{{$callback}}" />

  <table class="form">
    <tr>
      <th colspan="2" class="title">{{tr}}CConsultation-action-new patient moniroting{{/tr}}</th>
    </tr>
    <tr>
      <th style="width: 40%">{{mb_label object=$consult field="_datetime"}}</th>
      <td>
          {{mb_field object=$consult field="_datetime" canNull=false register=true form=addConsultSuiviPatient}}
      </td>
    </tr>

    <tr>
      <th class="notNull">{{mb_label object=$consult field="_prat_id"}}</th>

      <td>
        <select name="_prat_id" class="notNull ref"
                onchange="{{if 'oxCabinet'|module_active}}ConsultTamm.showSelectFunctionConsultImmediate(this.form){{/if}}">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>

            {{mb_include module=mediusers template=inc_options_mediuser selected=$app->user_id list=$praticiens}}
        </select>
      </td>
    </tr>

      {{if 'oxCabinet'|module_active}}
        <tr id="function">
        </tr>
      {{/if}}
    <tr>
      <td colspan="2" class="button">
        <div id="consult_exists">
          <button type="submit" class="new">{{tr}}CConsultation-action-Consult{{/tr}}</button>
        </div>
      </td>
    </tr>


  </table>
</form>
