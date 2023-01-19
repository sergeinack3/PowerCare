{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="small-info">
  {{tr}}CPatient-Select referent patient{{/tr}}
</div>

<form name="mergePatients" method="post">
  <input type="hidden" name="m" value="patients" />
  <input type="hidden" name="dosql" value="do_merge_link_patients" />
  <input type="hidden" name="patients_ids" value="{{$patients_ids}}" />
  <input type="hidden" name="status" value="{{$status}}" />
  <input type="hidden" name="link" value="{{$link}}" />

  <table class="form">
    <tr>
      <th class="title">
        {{$patient->_view}} [{{$patient->_IPP}}]
      </th>
    </tr>
    <tr>
      <td>
        {{foreach from=$patients item=_patient}}
          {{assign var=checked value=0}}
          {{if $_patient->_id === $patient_id_ref}}
            {{assign var=checked value=1}}
          {{/if}}
          {{mb_include module=patients template=inc_manage_identity_line
          type_input=radio
          onclick_input=""
          name_input=patient_id_referent}}
          <br />
        {{/foreach}}
      </td>
    </tr>
    <tr>
      <td class="button">
        <button type="button" class="big tick oneclick"
                onclick="IdentityValidator.submitForm(this.form)">
          {{tr}}Validate{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>
