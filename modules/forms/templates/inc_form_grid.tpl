{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=grid_colspan value=$ex_object->_ref_ex_class->getGridWidth()+1}}

{{assign var=_first_row value=$grid|@first}}
  {{assign var=_cols value=$_first_row|@first}}
  {{math equation="100/x" x=$_cols|@count assign=_pct}}

  {{foreach from=$groups item=_group}}
    {{foreach from=$_group->_ref_fields item=_field}}
      {{if $_field->hidden}}
        {{assign var=_field_name value=$_field->name}}
        <div class="field-{{$_field->name}}">
          {{mb_field object=$ex_object field=$_field_name hidden=true}}
        </div>
      {{/if}}
    {{/foreach}}
  {{/foreach}}

<table class="main form ex-form">
    {{foreach from=$_cols item=_row}}
      <col style="width: {{$_pct}}%;" />
    {{/foreach}}

  {{foreach from=$grid key=_group_id item=_grid}}
  {{if (is_countable($groups.$_group_id->_ref_fields) && $groups.$_group_id->_ref_fields|@count) ||
       (is_countable($groups.$_group_id->_ref_messages) && $groups.$_group_id->_ref_messages|@count) ||
       (is_countable($groups.$_group_id->_ref_host_fields) && $groups.$_group_id->_ref_host_fields|@count) ||
       (is_countable($groups.$_group_id->_ref_subgroups) && $groups.$_group_id->_ref_subgroups|@count)}}
  <tbody id="tab-{{$groups.$_group_id->_guid}}" style="display: none;">
  {{foreach from=$_grid key=_y item=_line}}
  <tr>
      {{foreach from=$_line key=_x item=_group name=_x}}
      {{if $_group.object}}
        {{if $_group.object|instanceof:'Ox\Mediboard\System\Forms\CExClassField'}}
          {{assign var=_field value=$_group.object}}
          {{assign var=_field_name value=$_field->name}}

          {{if !$_field->disabled && !$_field->hidden}}
            {{if $_group.type == "label"}}
              {{if $_field->coord_field_x == $_field->coord_label_x+1}}
                <th style="font-weight: bold; vertical-align: middle;">
                  <div class="text field-{{$_field->name}} field-label">
                    {{if $_field->_ref_hypertext_links}}
                      {{mb_include module=forms template=inc_vw_field_hypertext_links object=$_field ex_object=$ex_object field_name=$_field->name}}
                    {{else}}
                      {{mb_label object=$ex_object field=$_field_name}}
                    {{/if}}

                    {{mb_include module=forms template=inc_reported_value ex_object=$ex_object ex_field=$_field}}
                  </div>
                </th>
              {{else}}
                <td style="font-weight: bold; text-align: left;" class="field-{{$_field->name}} field-label">
                  <div class="text field-{{$_field->name}} field-label">
                    {{mb_label object=$ex_object field=$_field_name}}
                    {{mb_include module=forms template=inc_reported_value ex_object=$ex_object ex_field=$_field}}
                  </div>
                </td>
              {{/if}}
            {{elseif $_group.type == "field"}}
              <td {{if $_field->coord_field_x == $_field->coord_label_x+1}} style="vertical-align: middle;" {{/if}}>
                {{assign var=_style value=""}}
                  {{assign var=_properties value=$_field->_default_properties}}

                  {{foreach from=$_properties key=_type item=_value}}
                    {{if $_value != ""}}
                      {{assign var=_style value="$_style $_type:$_value;"}}
                    {{/if}}
                  {{/foreach}}

                <div class="field-{{$_field->name}} field-input" defaultstyle="1" style="{{$_style}}">
                  {{mb_include module=forms template=inc_ex_object_field ex_object=$ex_object ex_field=$_field form="editExObject_$ex_form_hash"}}
                </div>
              </td>
            {{/if}}
          {{/if}}
        {{elseif $_group.object|instanceof:'Ox\Mediboard\System\Forms\CExClassHostField'}}
          {{assign var=_host_field value=$_group.object}}

          {{if $_group.type == "label"}}
              {{assign var=_next_col value=$smarty.foreach._x.iteration}}
              {{assign var=_next value=null}}

              {{if array_key_exists($_next_col,$_line)}}
                {{assign var=_tmp_next value=$_line.$_next_col}}

                {{if $_tmp_next.object|instanceof:'Ox\Mediboard\System\Forms\CExClassHostField'}}
                  {{assign var=_next value=$_line.$_next_col.object}}
                {{/if}}
              {{/if}}

              {{if $_next && $_next->host_class == $_host_field->host_class && $_next->_field == $_host_field->_field}}
                <th style="font-weight: bold; vertical-align: top;">
              {{mb_label object=$_host_field->_ref_host_object field=$_host_field->_field}}
            </th>
          {{else}}
                <td style="font-weight: bold; text-align: left;">
                  {{mb_label object=$_host_field->_ref_host_object field=$_host_field->_field}}
                </td>
              {{/if}}
            {{else}}
            <td>
              {{if $_host_field->_ref_host_object->_id}}
                {{mb_value object=$_host_field->_ref_host_object field=$_host_field->_field}}
              {{elseif $preview_mode}}
                [{{mb_label object=$_host_field->_ref_host_object field=$_host_field->_field}}]
              {{else}}
                <div class="info empty opacity-30">Information non disponible</div>
              {{/if}}
            </td>
          {{/if}}
        {{else}}
          {{assign var=_message value=$_group.object}}

          {{if $_group.type == "message_title"}}
            {{if $_message->coord_text_x == $_message->coord_title_x+1}}
              <th style="font-weight: bold; vertical-align: middle;">
                {{if $_message->_ref_hypertext_links}}
                  {{mb_include module=forms template=inc_vw_field_hypertext_links object=$_message label=$_message->title}}
                {{else}}
                  {{$_message->title}}
                {{/if}}
              </th>
            {{else}}
              <td style="font-weight: bold; text-align: left;">
                {{if $_message->_ref_hypertext_links}}
                  {{mb_include module=forms template=inc_vw_field_hypertext_links object=$_message label=$_message->title}}
                {{else}}
                  {{$_message->title}}
                {{/if}}
              </td>
            {{/if}}
          {{else}}
            <td class="text">
              <div id="message-{{$_message->_guid}}">
                {{mb_include module=forms template=inc_ex_message ex_class=$ex_object->_ref_ex_class}}
              </div>
            </td>
          {{/if}}
        {{/if}}
      {{else}}
        <td></td>
      {{/if}}
    {{/foreach}}
  </tr>
  {{/foreach}}
  
  {{* Out of grid *}}
  {{foreach from=$groups.$_group_id->_ref_fields item=_field}}
    {{assign var=_field_name value=$_field->name}}
    
    {{if isset($out_of_grid.$_group_id.field.$_field_name|smarty:nodefaults) && !$_field->hidden && (!$_field->disabled || $ex_object->_id && $ex_object->$_field_name !== null)}}
      <tr>
        <th colspan="2" style="vertical-align: middle; font-weight: bold; width: 50%;">
          <div class="field-{{$_field->name}} field-label">
            {{mb_label object=$ex_object field=$_field->name}}
            {{mb_include module=forms template=inc_reported_value ex_object=$ex_object ex_field=$_field}}
          </div>
        </th>
        <td colspan="2" style="vertical-align: middle;">
          {{assign var=_style value=""}}
            {{assign var=_properties value=$_field->_default_properties}}

            {{foreach from=$_properties key=_type item=_value}}
              {{if $_value != ""}}
                {{assign var=_style value="$_style $_type:$_value;"}}
              {{/if}}
            {{/foreach}}

          <div class="field-{{$_field->name}} field-input" defaultstyle="1" style="{{$_style}}">
            {{mb_include module=forms template=inc_ex_object_field ex_object=$ex_object ex_field=$_field form="editExObject_$ex_form_hash"}}
          </div>
        </td>
      </tr>
    {{/if}}
  {{/foreach}}

  <tr>
    <td colspan="{{$grid_colspan}}" class="button">
      <button class="submit singleclick" type="submit" {{if $preview_mode}}disabled{{/if}}>{{tr}}Save{{/tr}}</button>

      {{if $ex_object->_id && $can_delete && !$preview_mode}}
        <button type="button" class="trash" onclick="confirmDeletion(this.form,{callback: (function(){ FormObserver.changes = 0; onSubmitFormAjax(this.form); }).bind(this), typeName:'', objName:'{{$ex_object->_ref_ex_class->name|smarty:nodefaults|JSAttribute}}'})">
          {{tr}}Delete{{/tr}}
        </button>
      {{/if}}
    </td>
  </tr>
    
  </tbody>
  {{/if}}
  {{/foreach}}
  
</table>
