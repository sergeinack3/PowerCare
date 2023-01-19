{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=field_name value=$ex_field->name}}

{{if $ex_field->report_class}}
  {{if $ex_object->_id}}
    <img src="./images/icons/reported.png" title="Valeur reportée ({{tr}}{{$ex_field->report_class}}{{/tr}})" />
  {{else}}
    {{assign var=reported_from value=null}}
    {{if array_key_exists($field_name, $ex_object->_reported_fields)}}
      {{assign var=reported_from value=$ex_object->_reported_fields.$field_name}}
    {{/if}}
    
    {{if $reported_from}}
      {{if $reported_from|instanceof:'Ox\Mediboard\System\Forms\CExObject'}}
        <img class="reported-icon" src="./images/icons/reported.png" style="outline: 0 solid green; background: #7f7;"
             title="Valeur reportée depuis {{$reported_from->_ref_ex_class->name}}&#10;{{mb_value object=$reported_from field=datetime_create}}&#10;{{$reported_from->_ref_object}}"  />
      {{else}}
        <img class="reported-icon" src="./images/icons/reported.png" style="outline: 0 solid blue; background: #77f;"
             title="Valeur reportée depuis {{$reported_from->_view}}"  />
      {{/if}}
    {{else}}
      <img class="reported-icon opacity-50" src="./images/icons/reported.png" title="Valeur non reportée" />
    {{/if}}
  {{/if}}
{{elseif $conf.forms.CExConcept.native_field}}
  {{assign var=_concept value=$ex_field->_ref_concept}}

  {{if $_concept && $_concept->native_field}}
    {{assign var=reported_from value=null}}
    {{if array_key_exists($field_name,$ex_object->_reported_fields)}}
      {{assign var=reported_from value=$ex_object->_reported_fields.$field_name}}
    {{/if}}

    {{if $ex_field->load_native_data}}
      <img src="./images/icons/reported.png" style="background: #a2bad6;" data-native_field="{{$_concept->native_field}}"
        {{if $reported_from}}
          title="Valeur reportée depuis {{tr}}{{$reported_from->_class}}{{/tr}} : {{$reported_from}}"
        {{/if}}
        class="native-field-report {{if !$reported_from}} opacity-50 {{/if}}"/>
    {{/if}}

    {{if $ex_field->update_native_data && !$ex_object->_id}}
      {{if
        !$ex_object->canReportRedon()
        && ($_concept->getNativeFieldClass() === 'CConstantesMedicales')
        && ('Ox\Mediboard\Patients\CRedon::isRedon'|static_call:$_concept->getNativeFieldName())
      }}
        {{* NO REPORT *}}
      {{else}}
        <img src="./images/icons/reported.png" style="background: #d63e39;"
             title="La valeur sera reportée dans {{$_concept->getNativeFieldView()}}" />
      {{/if}}
    {{/if}}
  {{/if}}
{{/if}}
