{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  merge = function (first_id, second_id) {
    var url = new Url("system", "object_merger").addParam("objects_class", "CPatient").addParam("objects_id", [first_id, second_id].join('-'));
    url.popup(900, 700);
  }
</script>

<tr>
  <th colspan="6" class="title">{{tr}}CPatient{{/tr}}</th>
</tr>

<tr>
  <th colspan="2">{{mb_label class=CPatient field=nom}}</th>
  <th>{{mb_title class=CPatient field=_IPP}}</th>
  <th>{{mb_title class=CPatient field=naissance}}</th>
  <th>{{mb_title class=CPatient field=_age}}</th>
  <th>{{mb_label class=CPatient field=adresse}}</th>
</tr>

{{foreach from=$conflicts item=_conflict}}
  {{assign var=patient value=$_conflict.patient}}
  {{assign var=patient_conflict value=$_conflict.patient_conflict}}
  <tr>
    <td rowspan="2" style="text-align: center">
      <button class="merge notext" onclick="merge('{{$patient->_id}}', '{{$patient_conflict->_id}}')"></button>
    </td>
    <td>
      <div class="text" id="{{$patient->_guid}}">
        <big onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">{{$patient}}</big>
      </div>
    </td>
    <td style="text-align: center">
      <strong>{{mb_include module=patients template=inc_vw_ipp ipp=$patient->_IPP}}</strong>
    </td>
    <td>
      <big>{{mb_value object=$patient field=naissance}}</big>
    </td>
    <td>
      {{mb_value object=$patient field=_age}}
    </td>
    <td>
      {{mb_value object=$patient field=adresse}}
      {{mb_value object=$patient field=cp}}
      {{mb_value object=$patient field=ville}}
    </td>
  </tr>
  <tr>
    <td>
      <div class="text" id="{{$patient_conflict->_guid}}">
        <big onmouseover="ObjectTooltip.createEx(this, '{{$patient_conflict->_guid}}')">{{$patient_conflict}}</big>
      </div>
    </td>
    <td style="text-align: center">
      <strong>{{mb_include module=patients template=inc_vw_ipp ipp=$patient_conflict->_IPP}}</strong>
    </td>
    <td>
      <big>{{mb_value object=$patient_conflict field=naissance}}</big>
    </td>
    <td>
      {{mb_value object=$patient_conflict field=_age}}
    </td>
    <td>
      {{mb_value object=$patient_conflict field=adresse}}
      {{mb_value object=$patient_conflict field=cp}}
      {{mb_value object=$patient_conflict field=ville}}
    </td>
  </tr>
{{/foreach}}