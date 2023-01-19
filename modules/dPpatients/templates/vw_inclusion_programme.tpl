{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  editInclusionProgramme = function (inclusion_programme_id, patient_id) {
    var url = new Url("patients", "ajax_edit_inclusion_programme");
    url.addParam("inclusion_programme_id", inclusion_programme_id);
    url.addParam("patient_id", patient_id);
    url.requestModal("30%", "55%", {onClose: Control.Modal.refresh});
  };
</script>

<table class="main">
  <tr>
    <td>
      <fieldset class="me-no-box-shadow">
        <legend>{{tr}}CProgrammeClinique-List of patient program|pl{{/tr}}</legend>
        {{if $patient->_can->edit}}
          <button class="new" type="button" onclick="editInclusionProgramme(0, '{{$patient->_id}}');">
            {{tr}}CProgrammeClinique-action-New protocol{{/tr}}
          </button>
        {{/if}}

        <div id="list_programmes_patient" style="height: 250px; overflow-y: auto;">
          <table class="tbl">
            <tr>
              <th>{{mb_label class=CProgrammeClinique field=nom}}</th>
              <th>{{mb_label object=$inclusion_programme field=date_debut}}</th>
              <th>{{mb_label object=$inclusion_programme field=date_fin}}</th>
              <th class="narrow"></th>
            </tr>
            {{foreach from=$inclusions_patient item=_inclusion_patient}}
              <tr>
                <td>{{$_inclusion_patient->_ref_programme_clinique->nom}}</td>
                <td>{{$_inclusion_patient->date_debut|date_format:$conf.date}}</td>
                <td>{{$_inclusion_patient->date_fin|date_format:$conf.date}}</td>
                <td>
                  {{if $patient->_can->edit}}
                    <button type="button" class="edit notext" title="{{tr}}common-action-Edit{{/tr}}"
                            onclick="editInclusionProgramme('{{$_inclusion_patient->_id}}', '{{$patient->_id}}');">
                      {{tr}}common-action-Edit{{/tr}}
                    </button>
                  {{/if}}
                </td>
              </tr>
              {{foreachelse}}
              <tr>
                <td colspan="4" class="empty">{{tr}}CProgrammeClinique-No program{{/tr}}</td>
              </tr>
            {{/foreach}}
          </table>
        </div>
      </fieldset>
    </td>
  </tr>
</table>
