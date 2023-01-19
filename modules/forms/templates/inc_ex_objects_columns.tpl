{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=_title_col value=10}}

{{assign var=_ex_obj value=$_ex_objects|@first}}

{{if !$ex_class_id || $search_mode}}
  <h3>{{$ex_classes.$_ex_class_id->name}}</h3>
{{/if}}

{{if $_ex_objects|@count}}
  {{unique_id var=ex_form_hash}}
  {{assign var=ex_form_hash value="ex_$ex_form_hash"}}

  {{assign var=ex_object value=$_ex_objects|@first}}

  <script>
    Main.add(function(){
      var form = getForm("editExObject_{{$ex_form_hash}}");

      new ExObjectFormula({{$formula_token_values|@json}}, form);
      ExObject.initPredicates({{$ex_object->_fields_default_properties|@json:true}}, {{$ex_object->_fields_display_struct|@json:true}}, form);
    });
  </script>

  {{mb_form name="editExObject_$ex_form_hash" m="system" dosql="do_ex_object_aed" method="post"
  onsubmit="return onSubmitFormAjax(this, function() { ExObject.loadExObjects('`$ex_object->object_class`', '`$ex_object->object_id`', '`$target_element`', 2, '`$_ex_class_id`'); })"}}
  {{mb_key object=$ex_object}}
  {{mb_field object=$ex_object field=_ex_class_id hidden=true}}
  {{mb_field object=$ex_object field=_event_name hidden=true}}
  {{mb_field object=$ex_object field=group_id hidden=true}}
  {{mb_field object=$ex_object field=_pictures_data hidden=true}}

  {{mb_field object=$ex_object field=object_class hidden=true}}
  {{mb_field object=$ex_object field=object_id hidden=true}}

  {{mb_field object=$ex_object field=reference_class hidden=true}}
  {{mb_field object=$ex_object field=reference_id hidden=true}}

  {{mb_field object=$ex_object field=reference2_class hidden=true}}
  {{mb_field object=$ex_object field=reference2_id hidden=true}}

  {{foreach from=$_ex_obj->_ref_ex_class->_ref_groups item=_group}}
    {{foreach from=$_group->_ref_fields item=_field}}
      {{if $_field->hidden}}
        {{assign var=_field_name value=$_field->name}}
        {{mb_field object=$ex_object field=$_field_name hidden=true}}
      {{/if}}
    {{/foreach}}
  {{/foreach}}

    <table class="main tbl me-margin-left-4 me-form-summary" style="width: 1%;">
      <!-- First line -->
      <thead>
      <tr style="font-size: 0.9em;">
        <th class="narrow"></th>
        <th {{if !$print}} style="min-width: 20em;" {{/if}}>Champ</th>

        {{foreach from=$_ex_objects item=_ex_object name=_ex_object}}
            {{if $smarty.foreach._ex_object.iteration != '1' }}
                {{if $date_tmp_first_line == $_ex_object->datetime_create|date_format:$conf.date }}
                    {{assign var=bold_first_line value=false}}
                {{else}}
                    {{assign var=date_tmp_first_line value=$_ex_object->datetime_create|date_format:$conf.date}}
                    {{assign var=bold_first_line value=true}}
                {{/if}}
            {{else}}
                {{assign var=date_tmp_first_line value=$_ex_object->datetime_create|date_format:$conf.date}}
                {{assign var=bold_first_line value=false}}
            {{/if}}
          <th class="narrow text" style="vertical-align: top; {{if $bold_first_line}} border-left:solid 2px #0288D1{{/if}}{{if !$_ex_object->_id}} min-width: 20em; {{/if}}">
            {{if $_ex_object->_id}}
              {{mb_value object=$_ex_object field=datetime_create}}
              {{assign var=_can_view value=$_ex_object->canPerm('v')}}

              {{if array_key_exists($reference_id,$alerts) && array_key_exists($_ex_class_id,$alerts.$reference_id)}}
                <span style="float: left; color: red;">
                  {{foreach from=$alerts.$reference_id.$_ex_class_id item=_alert}}
                    {{if $_alert.ex_object->_id == $_ex_object->_id}}
                      <span style="padding: 0 4px;" title="{{tr}}CExObject_{{$_alert.ex_class->_id}}-{{$_alert.ex_class_field->name}}{{/tr}}: {{$_alert.result}}">
                        {{mb_include module=forms template=inc_ex_field_threshold threshold=$_alert.alert title="none"}}
                      </span>
                    {{/if}}
                  {{/foreach}}
                </span>
              {{/if}}

              {{mb_include module=forms template=inc_ex_object_verified_icon ex_object=$_ex_object}}

              {{if !$print}}
                <hr />
                <div style="white-space: nowrap;">
                  {{assign var=buttons_list value=""}}
                  {{if !$search_mode && !$readonly}}
                    {{mb_ternary var=_button_attr test=$_ex_object->canPerm('e') value="" other="disabled"}}
                    {{me_button label=Edit icon=edit old_class="notext compact" attr=$_button_attr
                    onclick="ExObject.edit('`$_ex_object->_id`', '`$_ex_object->_ex_class_id`', '`$_ex_object->_ref_object->_guid`', '`$target_element`')"}}
                  {{/if}}

                  {{mb_ternary var=_button_attr test=$_can_view value="" other="disabled"}}
                  {{me_button label=Display icon=search old_class="notext compact" attr=$_button_attr
                  onclick="ExObject.display('`$_ex_object->_id`', '`$_ex_object->_ex_class_id`', '`$_ex_object->_ref_object->_guid`')"}}

                  {{me_button label=History icon=history old_class="notext compact" attr=$_button_attr
                  onclick="ExObject.history('`$_ex_object->_id`', '`$_ex_object->_ex_class_id`')"}}

                  {{me_button label=Print icon=print old_class="notext compact" attr=$_button_attr
                  onclick="ExObject.print('`$_ex_object->_id`', '`$_ex_object->_ex_class_id`', '`$_ex_object->_ref_object->_guid`')"}}

                  {{me_dropdown_button button_label=Actions button_icon="opt" button_class="notext me-tertiary me-small"
                  container_class="me-dropdown-button-right me-float-right"}}
                </div>
              {{/if}}

              {{if $_ex_object->additional_id}}
                <hr />
                <span onmouseover="ObjectTooltip.createEx(this, '{{$_ex_object->_ref_additional_object->_guid}}')">
                  {{$_ex_object->_ref_additional_object}}
                </span>
              {{/if}}
            {{else}}
              <button class="submit">{{tr}}Save{{/tr}}</button>
            {{/if}}
          </th>

          {{if $smarty.foreach._ex_object.iteration%$_title_col==0}}
            <th class="narrow"></th>
            <th>Champ</th>
          {{/if}}
        {{/foreach}}
      </tr>
      </thead>

      {{foreach from=$_ex_obj->_ref_ex_class->_ref_groups item=_ex_group name=_ex_group}}
        {{if !$_ex_group->disabled && $_ex_group->_ref_fields|@count}}

          <tbody class="data-row">
          <tr style="border-top: 2px solid #333;">
            {{math assign=rowspan equation="x+1" x=$_ex_group->_ranked_items|@count}}
            {{th_vertical rowspan=$rowspan class="ex_group"}}{{$_ex_group}}{{/th_vertical}}

            <td colspan="{{$_title_col+1}}" style="padding: 0;"></td>

            {{foreach from=$_ex_objects item=_ex_object name=_ex_object}}
              {{if $smarty.foreach._ex_object.iteration%$_title_col==0}}
                {{math assign=rowspan equation="x+1" x=$_ex_group->_ranked_items|@count}}
                {{th_vertical rowspan=$rowspan class="ex_group"}}{{$_ex_group}}{{/th_vertical}}

                <td colspan="{{$_title_col+1}}" style="padding: 0;"></td>
              {{/if}}
            {{/foreach}}
          </tr>

          {{foreach from=$_ex_group->_ranked_items item=_ex_field_or_message_or_host_field name=_ex_field}}
            {{if $_ex_field_or_message_or_host_field|instanceof:'Ox\Mediboard\System\Forms\CExClassField'}}
              {{assign var=_ex_field value=$_ex_field_or_message_or_host_field}}
              {{assign var=field_name value=$_ex_field->name}}

              <tr class="field {{if $_ex_field->_empty}} empty {{/if}} {{if $_ex_field->disabled || $_ex_field->hidden}} opacity-50 {{/if}}">
                <th class="text section" style="font-size: 0.9em; min-width: 12em;">
                  {{mb_label object=$_ex_obj field=$field_name}}
                </th>

                {{foreach from=$_ex_objects item=_ex_object2 name=_ex_object2}}
                  {{if $smarty.foreach._ex_object2.iteration != '1' }}
                      {{if $date_tmp == $_ex_object2->datetime_create|date_format:$conf.date }}
                          {{assign var=bold value=false}}
                      {{else}}
                          {{assign var=date_tmp value=$_ex_object2->datetime_create|date_format:$conf.date}}
                          {{assign var=bold value=true}}
                      {{/if}}
                  {{else}}
                      {{assign var=date_tmp value=$_ex_object2->datetime_create|date_format:$conf.date}}
                      {{assign var=bold value=false}}
                  {{/if}}
                  <td title="[{{mb_value object=$_ex_object2 field=datetime_create}}] {{tr}}{{$_ex_object2->_class}}-{{$field_name}}-court{{/tr}}" style="border-left:solid {{if $bold}} 2px #0288D1 {{else}} 1px #B0BEC5 {{/if}};{{if $_ex_object2->_id && ($_ex_object2->$field_name === null || $_ex_object2->$field_name === "")}} color: #aaa; {{/if}} {{if $_ex_field->formula}} font-weight: bold; {{/if}}"
                      class="{{if $_ex_object2->_id}} text {{/if}} text value {{if $_ex_object2->_specs.$field_name|instanceof:'Ox\Core\FieldSpecs\CTextSpec'}} compact {{/if}}">
                    {{if $_ex_object2->_id}}
                      {{if $_can_view}}
                        {{if array_key_exists($reference_id,$alerts) && array_key_exists($_ex_class_id,$alerts.$reference_id)}}
                          <span style="float: right; color: red;">
                          {{foreach from=$alerts.$reference_id.$_ex_class_id item=_alert}}
                            {{if $_alert.ex_object->_id === $_ex_object2->_id && $_alert.ex_class_field->_id === $_ex_field->_id}}
                              <span style="padding: 0 4px;" title="{{tr}}CExObject_{{$_alert.ex_class->_id}}-{{$_alert.ex_class_field->name}}{{/tr}}: {{$_alert.result}}">
                                {{mb_include module=forms template=inc_ex_field_threshold threshold=$_alert.alert title="none"}}
                              </span>
                            {{/if}}
                          {{/foreach}}
                        </span>
                        {{/if}}

                        {{mb_value object=$_ex_object2 field=$field_name}}
                      {{else}}
                        <div class="compact" style="text-align: center;" title="Visualisation non autorisée">
                          <i class="fa fa-ban"></i>
                        </div>
                      {{/if}}
                    {{else}}
                      {{if !($_ex_field->disabled || $_ex_field->hidden)}}
                        {{mb_include module=forms template=inc_reported_value ex_object=$ex_object ex_field=$_ex_field}}

                        {{mb_include
                        module=forms
                        template=inc_ex_object_field
                        ex_object=$ex_object
                        ex_field=$_ex_field
                        form="editExObject_$ex_form_hash"
                        hide_label=true
                        }}
                      {{/if}}
                    {{/if}}
                  </td>

                  {{if $smarty.foreach._ex_object2.iteration%$_title_col==0}}
                    <th class="text section" style="font-size: 0.9em; min-width: 12em;">
                      {{mb_label object=$_ex_obj field=$field_name}}
                    </th>
                  {{/if}}
                {{/foreach}}
              </tr>
            {{elseif $_ex_field_or_message_or_host_field|instanceof:'Ox\Mediboard\System\Forms\CExClassMessage'}}
              {{assign var=_ex_message value=$_ex_field_or_message_or_host_field}}

              <tr class="field">
                <th class="text category" colspan="{{$_title_col+1}}">
                  <div class="small-{{$_ex_message->type}}">
                    {{$_ex_message->text}}
                  </div>
                </th>

                {{foreach from=$_ex_objects item=_ex_object2 name=_ex_object2}}
                  {{if $smarty.foreach._ex_object2.iteration%$_title_col==0}}
                    <th class="text category" colspan="{{$_title_col+1}}">
                      <div class="small-{{$_ex_message->type}}">
                        {{$_ex_message->text}}
                      </div>
                    </th>
                  {{/if}}
                {{/foreach}}
              </tr>
            {{else}}
              {{assign var=_ex_host_field value=$_ex_field_or_message_or_host_field}}
              {{assign var=_field_name value=$_ex_host_field->_field}}

              <tr class="field {{if $_ex_host_field->_ref_host_object->$_field_name === null}} empty{{/if}}">
                <th class="text section" style="font-size: 0.9em; min-width: 12em;">
                  <img src="images/icons/info.png"
                       title="{{tr}}CExClassHostField-msg-Object displayed in tab may not have the same value as when the form was stored{{/tr}}" />

                  {{tr}}{{$_ex_host_field->host_class}}{{/tr}} &ndash;

                  {{if $_ex_host_field->_field == '_view'}}
                    Vue
                  {{elseif $_ex_host_field->_field == '_shortview'}}
                    Vue courte
                  {{else}}
                    {{tr}}{{$_ex_host_field->host_class}}-{{$_ex_host_field->_field}}{{/tr}}
                  {{/if}}
                </th>

                {{foreach from=$_ex_objects item=_ex_object2 name=_ex_object2}}
                  <td>
                    {{mb_value object=$_ex_object2->_ref_host_fields.$_field_name field=$_ex_host_field->_field}}
                  </td>
                {{/foreach}}
              </tr>
            {{/if}}
          {{/foreach}}
          </tbody>

          {{if !$smarty.foreach._ex_group.last}}
            <tr style="font-size: 0.9em;">
              <th class="narrow"></th>
              <th>Champ</th>

              {{foreach from=$_ex_objects item=_ex_object name=_ex_object}}
                  {{if $smarty.foreach._ex_object.iteration != '1' }}
                      {{if $date_tmp_midle_line == $_ex_object->datetime_create|date_format:$conf.date }}
                          {{assign var=bold_midle_line value=false}}
                      {{else}}
                          {{assign var=date_tmp_midle_line value=$_ex_object->datetime_create|date_format:$conf.date}}
                          {{assign var=bold_midle_line value=true}}
                      {{/if}}
                  {{else}}
                      {{assign var=date_tmp_midle_line value=$_ex_object->datetime_create|date_format:$conf.date}}
                      {{assign var=bold_midle_line value=false}}
                  {{/if}}
                <th class="narrow text" style="{{if $bold_midle_line}} border-left:solid 2px #0288D1{{/if}}">
                  {{mb_value object=$_ex_object field=datetime_create}}
                </th>
                {{if $smarty.foreach._ex_object.iteration%$_title_col==0}}
                  <th></th>
                  <th></th>
                {{/if}}
              {{/foreach}}
            </tr>
          {{/if}}

        {{/if}}
      {{/foreach}}
    </table>
  {{/mb_form}}
{{else}}
  <em>{{tr}}CExClass.none{{/tr}}</em>
{{/if}}
