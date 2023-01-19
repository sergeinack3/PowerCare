{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="cabinet" script="edit_consultation"}}

<script>
  Main.add(function () {
      {{if $consult->reunion_id}}
        Consultation.loadListePatientReunion({{$consult->reunion_id}});
      {{/if}}
  });
</script>

<table class="main layout me-align-auto">
  <tr>
    <td class="halfPane">
      {{if $consult->reunion_id}}
      <fieldset class="me-no-align">
        <legend>{{tr}}CPatient.list{{/tr}}</legend>
        <div id="liste-patient-reunion">{{* List of patients during a meeting (inc_patient_reunion) *}}</div>
      </fieldset>

      {{else}}
        <fieldSet class="me-no-align me-padding-left-4">
          <legend>{{tr}}CPatient.infos{{/tr}}</legend>
          <div class="text" id="infoPat">
            <div class="empty">{{tr}}CPatient.none_selected{{/tr}}</div>
          </div>
        </fieldSet>
      {{/if}}
    </td>

    {{* Documents *}}
    <td>
      {{if $consult->_id}}
        {{if !$consult->reunion_id && $consult->patient_id > 0}}
          {{mb_include module=cabinet template=inc_files_edit_consultation}}
        {{/if}}
      {{/if}}
    </td>
  </tr>
</table>

