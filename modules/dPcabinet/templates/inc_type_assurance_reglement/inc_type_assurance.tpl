{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
changeType = function() {
  Reglement.reload(function() {
    var url = new Url("cabinet", "ajax_type_assurance");
    url.addParam("consult_id", '{{$consult->_id}}');
    url.requestUpdate("area_type_assurance");
  });
}

</script>

<fieldset>
  <legend>{{tr}}type_assurance{{/tr}}</legend>
  {{mb_form name="editCslt-type_assurance" method="post" onsubmit="return onSubmitFormAjax(this);" m="cabinet" dosql="do_consultation_aed"}}
    {{mb_key object=$consult}}
    <input type="hidden" name="callback" value="changeType"/>

    {{if $consult->_ref_patient->is_smg}}
      <label><input type="radio" name="type_assurance" id="type_smg" value="smg" onclick="this.form.onsubmit();" {{if $consult->type_assurance == "smg"}} checked=true{{/if}}/>SMG</label>
    {{else}}
      <label><input type="radio" name="type_assurance" id="type_classique" value="classique" onclick="this.form.onsubmit();" {{if $consult->type_assurance == "classique"}} checked=true{{/if}}/>Assurance Maladie</label>
      <label><input type="radio" name="type_assurance" id="type_at" value="at" onclick="this.form.onsubmit();" {{if $consult->type_assurance == "at"}} checked=true{{/if}}/>Accident du travail</label>

      {{if "maternite"|module_active && $consult->grossesse_id}}
        <label><input type="radio" name="type_assurance" id="type_maternite" value="maternite" onclick="this.form.onsubmit();" {{if $consult->type_assurance == "maternite"}} checked=true{{/if}}/>Maternité</label>
      {{/if}}
    {{/if}}
  {{/mb_form}}
</fieldset>
