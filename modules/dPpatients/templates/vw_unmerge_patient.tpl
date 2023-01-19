{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPpatients script=patient_unmerge ajax=true}}

<script>
  Main.add(function () {
    var form = getForm('show-old-patient-infos');
    form.onsubmit();
    form = getForm('show-new-patient-infos');
    form.onsubmit();
    PatientUnmerge.old_patient_id = {{$patient->_id}};
  });
</script>

<table class="main layout">
  <tr>
    <td style="width: 50%">
      <form name="show-old-patient-infos" method="get" onsubmit="return onSubmitFormAjax(this, null, 'old-patient-infos')">
        <input type="hidden" name="m" value="dPpatients" />
        <input type="hidden" name="a" value="ajax_vw_unmerge_patient" />
        <input type="hidden" name="patient_id" value="{{$patient->_id}}" />
        <input type="hidden" name="step" value="old" />
      </form>

      <div id="old-patient-infos"></div>
    </td>
    <td style="width: 50%">
      <form name="show-new-patient-infos" method="get" onsubmit="return onSubmitFormAjax(this, null, 'new-patient-infos')">
        <input type="hidden" name="m" value="dPpatients" />
        <input type="hidden" name="a" value="ajax_vw_unmerge_patient" />
        <input type="hidden" name="patient_id" value="{{$patient->_id}}" />
        <input type="hidden" name="step" value="new" />
      </form>

      <div id="new-patient-infos"></div>
    </td>
  </tr>
</table>