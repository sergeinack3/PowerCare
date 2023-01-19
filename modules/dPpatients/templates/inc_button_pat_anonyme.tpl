{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=form value=""}}
{{mb_default var=patient_id value=""}}
{{mb_default var=other_form value=""}}
{{mb_default var=callback value=""}}
{{mb_default var=input_name value="patient_id"}}

<script>
  createPatAnonyme = function () {
    if (confirm($T('CPatient-create-anonymous-patient-confirm'))) {
      var url = new Url("patients", "do_anonymous_patient", "dosql");
      url.addParam("callback", "fillPatAnonyme");
      url.requestUpdate("systemMsg", {method: "post"});
    }
    return false;
  };

  fillPatAnonyme = function (pat_id, pat) {
    var form = getForm('{{$form}}');

    if (form) {
      $V(form.{{$input_name}}, pat_id);
      $V(form._patient_view, pat._view);
      if (form._patient_sexe) {
        $V(form._patient_sexe, pat.sexe);
      }
    }

    var otherForm = getForm("{{$other_form}}");

    if (otherForm) {
      $V(otherForm.{{$input_name}}, pat_id);
      $V(otherForm._patient_view, pat._view);
      if (otherForm._patient_sexe) {
        $V(otherForm._patient_sexe, pat.sexe);
      }
    }

    {{if $callback}}
    {{$callback}}();
    {{/if}}
  }
</script>

{{if "dPpatients CPatient allow_anonymous_patient"|gconf && !$patient_id}}
  <button type="button" class="anonyme notext me-tertiary" onclick="createPatAnonyme()"></button>
{{/if}}