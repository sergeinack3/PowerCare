{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var patientForm = getForm("export-patients-form");
    Calendar.regField(patientForm.date_min);
    Calendar.regField(patientForm.date_max);
  });
</script>

<div style="width: 15%; display: inline-block; float: left;" class="me-float-none me-width-auto">
  {{mb_include module=dPpatients template=inc_multi_select_mediusers form_name='export-patients-form' form_select='praticien_id' lite=true}}
</div>

<div style="display: inline-block; float: left;" class="me-float-none me-width-auto me-valign-top">
  <form name="export-patients-form" method="get" target="_blank">
    <input type="hidden" name="m" value="dPpatients" />
    <input type="hidden" name="raw" value="ajax_export_patients_csv" />

    <select name="praticien_id[]" multiple style="display: none;">
      {{foreach from=$praticiens item=_prat}}
        <option value="{{$_prat->_id}}">{{$_prat}}</option>
      {{/foreach}}
    </select>

    <table class="main layout">
      <tr>
        <th>
          <label for="date_min">{{tr}}dPpatients-export-Date min{{/tr}}</label>
        </th>
        <td>
          <input type="hidden" name="date_min" value="" />
        </td>

        <th>
          <label for="date_max">{{tr}}dPpatients-export-Date max{{/tr}}</label>
        </th>
        <td>
          <input type="hidden" name="date_max" value="" />
        </td>
      </tr>

      <tr>
        <th><label for="patient_id">{{tr}}CPatient-patient_id{{/tr}}</label></th>
        <td><input type="number" size="5" name="patient_id" /></td>

        <th><label for="all_prats">{{tr}}dPpatients-export all prats{{/tr}}</label></th>
        <td><input type="checkbox" name="all_prats" /></td>
      </tr>

      <tr>
        <td colspan="4" class="button">
          <button class="fas fa-external-link-alt">{{tr}}Export{{/tr}}</button>
        </td>
      </tr>
    </table>

  </form>
</div>