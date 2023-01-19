{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  showPrescriptionLines = function (inclusion_programme_id) {
    var url = new Url("patients", "ajax_vw_prescription_lines_programme");
    url.addParam("inclusion_programme_id", inclusion_programme_id);
    url.requestModal("50%", "50%");
  };
</script>

<fieldset>
  <legend>{{tr}}CInclusionProgramme-List of patients included in this program{{/tr}}</legend>

  <table class="main tbl">
    <tr>
      <th>{{mb_label class=CInclusionProgramme field=patient_id}}</th>
      <th class="narrow">{{tr}}CPrescription{{/tr}}</th>
    </tr>

    {{foreach from=$includes_programme item=_include_programme}}
      {{assign var=patient value=$_include_programme->_ref_patient}}
      <tr>
        <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">
            {{$patient->_view}}
          </span>
        </td>
        <td class="button">
          <button type="button" class="search notext"
                  onclick="showPrescriptionLines('{{$_include_programme->_id}}', '{{$patient->_id}}');"
                  title="{{tr}}CInclusionProgrammeLine-action-See the prescription line of the program|pl{{/tr}}">
            {{tr}}common-action-Edit{{/tr}}
          </button>
        </td>
      </tr>
      {{foreachelse}}
      <tr>
        <td class="empty" colspan="2">{{tr}}CPatient.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  </table>
</fieldset>
