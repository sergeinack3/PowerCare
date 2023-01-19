{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}


{{foreach from=$logs item=_log}}
  {{assign var=ref_object value=$_log->_ref_object}}
  <tbody class="hoverable">
  <tr {{if $_log->type != "store"}} style="font-weight: bold" {{/if}}>
    {{assign var=field_count value=0}}
    {{if $_log->_fields}}
      {{assign var=field_count value=$_log->_fields|@count}}
    {{/if}}
    {{assign var=rowspan value=$field_count|ternary:$field_count:1}}
    <td rowspan="{{$field_count}}" style="text-align: center;">
      <label onmouseover="ObjectTooltip.createEx(this, '{{$_log->_ref_user->_guid}}');">
        {{mb_ditto name=user value=$_log->_ref_user->_view}}
      </label>
    </td>
    <td rowspan="{{$field_count}}" style="text-align: center;">
      {{mb_ditto name=date value=$_log->date|date_format:$conf.date}}
    </td>
    <td rowspan="{{$field_count}}" style="text-align: center;">
      <span title="{{$_log->date|iso_time}}">{{mb_ditto name=time value=$_log->date|date_format:$conf.time}}</span>
    </td>
    {{if $app->user_prefs.displayUTCDate}}
      <td rowspan="{{$field_count}}" class="narrow"
          style="text-align: center;">{{mb_ditto name=utc_date value=$_log->date|utc_datetime}}</td>
    {{/if}}
    <td rowspan="{{$field_count}}" {{*if $_log->type != "store"}}colspan="1"{{/if*}}
      {{if $field_count == 0 && $object->_id}} colspan="4" {{/if}}>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_log->_guid}}')">
            {{$_log->getTypeIco()|smarty:nodefaults}}
            {{mb_value object=$_log field=type}}

          </span>
    </td>

    {{if $object->_id}}
    {{foreach from=$_log->_fields item=_field name=field}}

    <td class="text" style="font-weight: normal;">
      {{if array_key_exists($_field, $object->_specs)}}
      {{if $object->_specs[$_field]->derived}}
    <td colspan="3" style="display: none;"></td>
    {{else}}
    {{mb_label object=$object field=$_field}}

    {{/if}}
    {{else}}
    {{tr}}CMbObject.missing_spec{{/tr}} ({{$_field}})
    {{/if}}
    </td>
    {{* DIFF for CTextSpec *}}
    {{ if isset($_log->_diff_values[$_field]|smarty:nodefaults) }}
      <td colspan="2" class="text">
        {{ $_log->_diff_values[$_field]|smarty:nodefaults }}
      </td>
    {{else}}
      {{if array_key_exists($_field,$_log->_old_values)}}
        <td class="text" style="font-weight: normal;">
          <!-- old -->
          {{assign var=old_value value=$_log->_old_values.$_field}}
          {{if property_exists($object, $_field)}}
            {{if $old_value === ''}}
              <i class="me-icon cancel me-warning"></i>
              {{else}}
              <del>
                {{mb_value object=$object field=$_field value=$old_value tooltip=1}}
              </del>
            {{/if}}

            <span style="padding-left: 2px; padding-right: 2px;">&#8594;</span>
          {{/if}}

          <!-- new -->
          {{assign var=log_id value=$_log->_id}}
          {{if isset($object->_history.$log_id.$_field|smarty:nodefaults)}}
            {{assign var=new_value value=$object->_history.$log_id.$_field}}
            <ins>
              {{*
                Pour le log le plus récent, si c est un champ qui a été supprimé (donc qui vaut null) :
                dans la fonction mb_value le champ value est utilisé seulement s il est différent de null.
                Donc affectation d une chaîne vide au lieu de null
              *}}
              {{if $new_value === null}}
                {{assign var=new_value value=""}}
              {{/if}}
              {{if property_exists($object, $_field)}}
                {{if $new_value === ''}}
                  <i class="me-icon cancel me-error"></i>
                  {{else}}
                  {{mb_value object=$object field=$_field value=$new_value tooltip=1}}
                {{/if}}

              {{/if}}
            </ins>
            {{else}}
            <i class="me-icon cancel me-error"></i>
          {{/if}}
        </td>
      {{else}}
        <td colspan="3" class="empty" style="font-weight: normal;">{{tr}}Unavailable information{{/tr}}</td>
      {{/if}}
    {{/if}}

    {{if !$smarty.foreach.field.last}}</tr>
  <tr>{{/if}}
    {{foreachelse}}
    <td colspan="3" rowspan="{{$field_count}}"></td>
    {{/foreach}}
    {{else}}
    <!-- Nom de champs modifiée -->
    {{if $_log->type == "store"}}
      <td class="text">
        {{if $_log->object_class|strpos:"CExObject_" === false}} {{* Because the object can t be instanciated *}}
          {{foreach from=$_log->_fields item=_field name=field}}
            {{if array_key_exists($_field, $ref_object->_specs)}}
              {{mb_label class=$_log->object_class field=$_field}}
            {{else}}
              {{tr}}CMbObject.missing_spec{{/tr}} ({{$_field}})
            {{/if}}
            {{if !$smarty.foreach.field.last}} - {{/if}}
          {{/foreach}}
        {{/if}}
      </td>
    {{/if}}
    {{/if}}
  </tr>
  </tbody>
  {{foreachelse}}
  <tr>
    <td colspan="20" class="empty">{{tr}}CUserLog.none{{/tr}}</td>
  </tr>
{{/foreach}}
