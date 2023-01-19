{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=type_input value="checkbox"}}
{{mb_default var=name_input value="patient_id[]"}}
{{mb_default var=onclick_input value="IdentityValidator.toggleActionButton();"}}
{{mb_default var=can_merge value=0}}
{{mb_default var=checked value=0}}

<div>
  <label>
    <input type="{{$type_input}}" name="{{$name_input}}" value="{{$_patient->_id}}"
           onclick="{{$onclick_input}}" {{if $checked}}checked{{/if}} />
    {{$_patient->_view}} [{{$_patient->_IPP}}]
  </label>

  <div class="compact">
    <table class="main">
      <tr>
        <td style="width: 33%;">
          {{mb_value object=$_patient field=naissance}} ({{$_patient->_age}})
        </td>
        <td style="width: 33%;">
          {{mb_label object=$_patient field=tel2}} :
          <br />
          {{mb_value object=$_patient field=tel2}}
        </td>
        <td>
          {{mb_label object=$_patient field=adresse}} :
          <br />
          {{mb_value object=$_patient field=adresse}}
          {{mb_value object=$_patient field=cp}} {{mb_value object=$_patient field=ville}}
        </td>
      </tr>
    </table>
  </div>
</div>