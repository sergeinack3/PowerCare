{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $object->_id && !$object->_can->read}}
  <div class="small-info">
    {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

<table class="tbl">
  <tr>
    <th class="title text">
      {{mb_include module=system template=inc_object_idsante400}}
      {{mb_include module=system template=inc_object_history}}
      {{mb_include module=system template=inc_object_notes}}

      {{$object}}
    </th>
  </tr>
  <tr>
    <td>
      <strong>{{mb_label object=$object field=patient_id}}</strong> :
      {{mb_value object=$object field=patient_id tooltip=true}}
      <br />
      {{if $object->practitioner_name}}
        <strong>{{mb_label object=$object field=practitioner_name}}</strong> :
        {{mb_value object=$object field=practitioner_name}}
        <br />
      {{/if}}

      {{if $object->injection_date}}
        <strong>{{mb_label object=$object field=injection_date}}</strong> :
        {{mb_value object=$object field=injection_date}}
        <br />
      {{/if}}

      <strong>{{mb_label object=$object field=recall_age}}</strong> :
      {{$object->_recall_age_string}}
      <br />

      {{if $object->batch}}
        <strong>{{mb_label object=$object field=batch}}</strong> :
        {{mb_value object=$object field=batch}}
        <br />
      {{/if}}

      {{if $object->speciality}}
        <strong>{{mb_label object=$object field=speciality}}</strong> :
        {{mb_value object=$object field=speciality}}
        <br />
      {{/if}}

      {{if $object->remarques}}
        <strong>{{mb_label object=$object field=remarques}}</strong> :
        {{mb_value object=$object field=remarques}}
        <br />
      {{/if}}

      {{if $object->cip_product}}
        <strong>{{mb_label object=$object field=cip_product}}</strong> :
        {{mb_value object=$object field=cip_product}}
        <br />
      {{/if}}

      {{if $object->expiration_date}}
        <strong>{{mb_label object=$object field=expiration_date}}</strong> :
        {{mb_value object=$object field=expiration_date}}
        <br />
      {{/if}}

    </td>
  </tr>
</table>

<button class="edit" onclick="Vaccination.editVaccinationView({{$object->_id}})">
    {{tr}}Modify{{/tr}}
</button>
