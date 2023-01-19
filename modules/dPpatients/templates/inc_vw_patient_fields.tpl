{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=use_history value=false}}

{{foreach from=$patient_fields key=_field_name item=_field_value}}
  <tr {{if !$_field_value}}class="none" style="display: none"{{/if}}>
    {{if $use_history && array_key_exists($_field_name, $updated_fields)}}
      <td class="narrow">
        <a onclick="PatientUnmerge.fieldHistory('{{$patient->_id}}', '{{$_field_name}}');"
           onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}', 'fieldHistory', {field: '{{$_field_name}}'})">
          {{me_img src="history.gif" width=16 height=16 icon="history" class="me-primary"}}
        </a>
      </td>
    {{else}}
      <td class="narrow"></td>
    {{/if}}

    <th>
      <strong>
        {{mb_label object=$patient field=$_field_name}}
      </strong>
    </th>

    {{assign var=field_spec value=$specs[$_field_name]}}
    <td>
      {{if $field_spec|instanceof:'Ox\Core\FieldSpecs\CDateTimeSpec' || $field_spec|instanceof:'Ox\Core\FieldSpecs\CBirthDateSpec' || $field_spec|instanceof:'Ox\Core\FieldSpecs\CDateSpec' || $field_spec|instanceof:'Ox\Core\FieldSpecs\CTimeSpec'}}
        {{mb_field object=$patient field=$_field_name form=$form register=true}}
      {{else}}
        {{mb_field object=$patient field=$_field_name}}
      {{/if}}
    </td>
  </tr>
{{/foreach}}