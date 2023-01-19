{{*
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="addEditPrescription" method="post" onsubmit="return onSubmitFormAjax(this);">
  <input type="hidden" name="m" value="labo" />
  <input type="hidden" name="dosql" value="do_prescription_aed" />
  <input type="hidden" name="prescription_labo_id" value="{{$prescription->_id}}" />
  <input type="hidden" name="callback" value="Prescription.select" />
  <input type="hidden" name="del" value="0" />

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$prescription}}

    <tr>
      <th>{{mb_label object=$prescription field="patient_id"}}</th>
      <td>{{mb_field object=$prescription field="patient_id" hidden="hidden"}}{{$prescription->_ref_patient}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$prescription field="date"}}</th>
      <td>{{mb_field object=$prescription field="date" form="addEditPrescription" register=true}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$prescription field="praticien_id"}}</th>
      <td>
        <select name="praticien_id">
          {{mb_include module=mediusers template=inc_options_mediuser selected=$prescription->praticien_id list=$listPrats}}
        </select>
      </td>
    </tr>
    <tr>
      <th class="title" colspan="2">{{tr}}common-Other information|pl{{/tr}}</th>
    </tr>
    <tr>
      <td>{{tr}}CPrisePosologie-_urgent{{/tr}}:</td>
      <td>{{mb_field object=$prescription field=urgence}}</td>
    </tr>

    <tr>
      <td colspan="2" class="button">
        <button type="button" class="submit" onclick="this.form.onsubmit();">
          {{tr}}Validate{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>