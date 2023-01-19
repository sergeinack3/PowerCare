{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=hermetic_mode value='Ox\Mediboard\System\Forms\CExClass::inHermeticMode'|static_call:false}}

{{if $object->_id}}
  <button type="button" class="new me-margin-left-6" onclick="MbObject.edit('{{$object->_class}}-0')">
    {{tr}}{{$object->_class}}-title-create{{/tr}}
  </button>
{{/if}}

{{mb_script module=forms script=ex_class_editor ajax=true}}
{{mb_script module=forms script=ex_class_layout_editor ajax=true}}
{{mb_script module=forms script=ex_class_layout_editor_pixel ajax=true}}
{{mb_script module=system script=object_selector ajax=true}}
{{mb_script module=files script=file ajax=true}}
{{if "appFineClient"|module_active}}
  {{mb_script module=appFineClient script=appFineClient ajax=true}}
{{/if}}
{{assign var=form_name value="edit-`$object->_guid`"}}

<script type="text/javascript">
restrictGroup = function() {
  var form = getForm("{{$form_name}}");
  var group_id = $V(form.elements.group_id);
  var categories_select = form.elements.category_id;
  $A(categories_select.options).each(function(option){
    var active = !option.value || option.get("group_id") == group_id;
    option.setVisible(active);
    option.disabled = !active;

    if (!active) {
      option.selected = false;
    }
  });
};

Main.add(function(){
  ExClass.id = "{{$object->_id}}";
  ExClass.layourEditorReady = false;

  ExFieldNotification.loadList();

  restrictGroup();
});
</script>

<table class="main form">
  {{mb_include module=system template=inc_form_table_header object=$object colspan="2" css_class="text"}}

  <tr>
    <td colspan="2">
      <form name="{{$form_name}}" method="post" action="?" onsubmit="return onSubmitFormAjax(this)">
        <input type="hidden" name="m" value="system" />
        <input type="hidden" name="dosql" value="do_ex_class_aed" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="_duplicate" value="" />
        <input type="hidden" name="callback" value="MbObject.editCallback" />
        {{mb_key object=$object}}
        
        <table class="main form me-no-align me-no-box-shadow">
          <tr>
            <th>{{mb_label object=$object field=name}}</th>
            <td>{{mb_field object=$object field=name style="width: 95%;"}}</td>
            
            <th>{{mb_label object=$object field=conditional}}</th>
            <td>{{mb_field object=$object field=conditional typeEnum=checkbox}}</td>
          </tr>
          
          <tr>
            <th>
                {{mb_label object=$object field=group_id}}
            </th>
            <td>

                {{if $hermetic_mode}}
                    {{if $object->_id}}
                        {{mb_value object=$object field=group_id tooltip=true}}
                    {{else}}
                      <select name="group_id" style="width: 20em;" onclick="restrictGroup()">
                          {{if $app->_ref_user->isAdmin()}}
                            <option value=""> &ndash; Tous </option>
                          {{/if}}

                          {{foreach from=$object->_groups item=_group}}
                            <option value="{{$_group->_id}}" {{if $object->group_id == $_group->_id}} selected="selected" {{/if}}>{{$_group}}</option>
                          {{/foreach}}
                      </select>
                    {{/if}}
                {{else}}
                  <select name="group_id" style="width: 20em;" onclick="restrictGroup()">
                    <option value=""> &ndash; Tous </option>

                      {{foreach from=$object->_groups item=_group}}
                        <option value="{{$_group->_id}}" {{if $object->group_id == $_group->_id}} selected="selected" {{/if}}>{{$_group}}</option>
                      {{/foreach}}
                  </select>
                {{/if}}

            </td>
          
            <th rowspan="2">{{mb_label object=$object field=native_views}}</th>
            <td rowspan="2">{{mb_field object=$object field=native_views}}</td>
          </tr>
          
          {{if $object->_id}}
            <tr>
              <th>
                <label for="_bind_tag_view">{{tr}}CTag|pl{{/tr}}</label>
              </th>

              <td style="white-space: normal;">
                  {{if $hermetic_mode && !$app->_ref_user->isAdmin()}}
                      {{if !$object->group_id}}
                          {{mb_include module=system template=inc_tag_binder_widget show_button=false readonly=true}}
                      {{else}}
                          {{mb_include module=system template=inc_tag_binder_widget show_button=false}}
                      {{/if}}
                  {{else}}
                      {{mb_include module=system template=inc_tag_binder_widget show_button=true}}
                  {{/if}}
              </td>
            </tr>
          {{else}}
            <tr>
              <td colspan="2"></td>
            </tr>
          {{/if}}

          <tr>
            <th>{{mb_label object=$object field=category_id}}</th>
            <td>
              <select name="category_id" class="">
                <option value="">&ndash; {{tr}}CExClassCategory.none{{/tr}}</option>

                {{foreach from=$object->_categories item=_category}}
                  <option value="{{$_category->_id}}" {{if $_category->_id == $object->category_id}}selected{{/if}} data-group_id="{{$_category->group_id}}">
                    {{$_category}}
                  </option>
                {{/foreach}}
              </select>
            </td>

            <th>{{mb_label object=$object field=cross_context_class}}</th>
            <td>{{mb_field object=$object field=cross_context_class emptyLabel="None"}}</td>
          </tr>
          
          <tr>
            <th>
              {{mb_label object=$object field=allow_create_in_column}}
            </th>
            <td style="vertical-align: bottom; text-align: right;">
              <span style="float: left;">
                {{mb_field object=$object field=allow_create_in_column typeEnum=checkbox}}
              </span>

              {{if $object->_id}}
                <button type="button" class="search" onclick="(new Url('forms', 'ajax_ex_class_preview_events')).addParam('ex_class_id', '{{$object->_id}}').requestModal(300, 200)">{{tr}}Preview{{/tr}}</button>
                {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
                  <span id="appFine_button_preview_form">
                    {{mb_include module=appFineClient template=inc_button_preview_form object_id=$object->_id file_name=$object->name}}
                  </span>
                {{/if}}
                <button type="submit" class="modify">{{tr}}Save{{/tr}}</button>
                {{me_button icon=duplicate label=Duplicate
                            onclick="if (confirm('Confirmez-vous la duplication de ce formulaire ?')) { \$V(this.form._duplicate, 1); this.form.onsubmit(); }"}}
                {{assign var=object_view value=$object->_view|smarty:nodefaults|JSAttribute}}
                {{me_button icon=trash label=Delete
                            onclick="confirmDeletion(this.form,{ajax:true,typeName: \$T('CExClass.one'),objName:'$object_view'})"}}
                {{me_button icon=change label=Export onclick="ExClass.exportObject('`$object->_id`')"}}
                {{me_button icon="far fa-hand-paper" label=Permissions onclick="ExClass.editPermissions('`$object->_id`')"}}
                {{me_dropdown_button button_icon=opt button_label=Opt button_class="me-tertiary notext"}}
              {{else}}
                <button type="submit" class="submit">{{tr}}Save{{/tr}}</button>
              {{/if}}
            </td>

            {{if $conf.forms.CExClass.pixel_positionning}}
              <th>{{mb_label object=$object field=pixel_positionning}}</th>
              <td>
                {{mb_field object=$object field=pixel_positionning typeEnum=checkbox}}

                {{if $conf.forms.CExClass.allowing_additional_columns}}
                  <span class="form-additional-columns-field">
                    {{mb_label object=$object field=nb_columns}}
                    {{mb_field object=$object field=nb_columns form=$form_name increment=true}}
                  </span>
                {{/if}}
              </td>
            {{elseif $conf.forms.CExClass.allowing_additional_columns}}
                <th>
                  <div class="form-additional-columns-field">
                    {{mb_label object=$object field=nb_columns}}
                  </div>
                </th>

                <td>
                  <div class="form-additional-columns-field">
                    {{mb_field object=$object field=nb_columns form=$form_name increment=true}}
                  </div>
                </td>
            {{else}}
              <td colspan="2"></td>
            {{/if}}
          </tr>
        </table>
      </form>
    </td>
  </tr>
</table>

{{if $object->_id}}

<script type="text/javascript">
Main.add(function(){
  exClassTabs = Control.Tabs.create("ExClass-back", true, {
    afterChange: function(newContainer){
      if (ExClass.layourEditorReady || newContainer.id != "fields-layout") {
        return;
      }
      
      // Supprimé à cause des widgets qui étaient preparées autant de fois qu'on change de volet
      /*var form = getForm("form-grid-layout").removeClassName("prepared");
      prepareForm(form);*/
      
      {{if $object->pixel_positionning}}
        ExClass.initPixelLayoutEditor(ExClass.startDrag, ExClass.endDrag);

        newContainer.select(".tab-action-buttons .draggable").each(ExClass.initDraggableActionButton);
        newContainer.select(".tab-widgets .draggable").each(ExClass.initDraggableWidget);
      {{else}}
        ExClass.initLayoutEditor();
      {{/if}}
    }
  });
  
  Control.Tabs.create("field_groups", true);
});

toggleGroupLabelEdit = function(link) {
  link = $(link);
  link.down('span.label').toggle();
  link.up('li').classList.toggle('me-tabs-inline-flex');
  
  var form = link.down('form');
  var input = form.elements.name;
  
  form.toggle();
  input.select();
}
</script>

<ul class="control_tabs me-align-auto" id="ExClass-back">
  <li><a href="#fields-specs" class="active">{{tr}}CExClassFieldGroup-back-class_fields{{/tr}}</a></li>
  <li><a href="#fields-layout">{{tr}}CExClassField-layout{{/tr}}</a></li>
  <li>
    <a href="#fields-events" {{if !$object->_ref_events}} class="empty" {{/if}}>
      {{tr}}CExClass-back-events{{/tr}}
      <small>({{$object->_ref_events|@count}})</small>
    </a>
  </li>
  <li>
    <a href="#exclass-notifications" {{if !$object->_ref_ex_notifications}} class="empty" {{/if}}>
      {{tr}}CExClassFieldPredicate-back-ex_notifications{{/tr}}
    </a>
  </li>
</ul>

<div id="fields-specs" class="me-align-auto me-padding-0">

<ul class="control_tabs me-control-tabs-wraped me-margin-top-0" id="field_groups" style="font-size: 0.9em;">
  {{foreach from=$object->_ref_groups item=_group name=_groups}}
    <li {{if $_group->disabled}} class="opacity-50" style="font-style: italic;" {{/if}}>
      <a href="#group-{{$_group->_guid}}" ondblclick="toggleGroupLabelEdit(this)" title="Double-cliquer pour modifier" 
         style="padding: 2px 4px;">
        <span class="label" style="font-weight: normal;">
          {{$_group->name}} <small>({{$_group->_ref_fields|@count}})</small>
        </span>

        <form class="me-small-fields" name="edit-field-group-{{$_group->_guid}}" action="?" method="post" style="display: none;"
              onsubmit="return onSubmitFormAjax(this, ExClass.edit.curry({{$object->_id}}))">
          <input type="hidden" name="m" value="system" />
          <input type="hidden" name="del" value="0" />
          <input type="hidden" name="_move" value="" />
          <input type="hidden" name="@class" value="CExClassFieldGroup" />
          {{mb_key object=$_group}}
          {{mb_field object=$_group field=disabled hidden=true}}

          {{if !$smarty.foreach._groups.first}}
            <button type="button" class="left notext compact" onclick="$V(this.form._move, 'before'); this.form.onsubmit()" 
                    title="Déplacer sur la gauche"></button>
          {{/if}}

          {{mb_field object=$_group field=name size=18}}
          {{if !$smarty.foreach._groups.last}}
            <button type="button" class="right notext compact" onclick="$V(this.form._move, 'after'); this.form.onsubmit()" 
                    title="Déplacer sur la droite"></button>
          {{/if}}

          <button onclick="Event.stop(event); this.form.onsubmit();"
                  class="submit notext compact compact" type="submit">
            {{tr}}Save{{/tr}}
          </button>
          
          <input type="checkbox" {{if !$_group->disabled}} checked {{/if}}
                 title="{{tr}}CExClassFieldGroup-_enabled{{/tr}}"
                 onclick="ExClass.disableGroup(event)" />

          {{if $_group->_ref_fields|@count == 0}}
            <button onclick="return confirmDeletion(this.form, {ajax: true}, ExClass.edit.curry({{$object->_id}}))"
                    class="trash notext compact" type="button">
              {{tr}}Delete{{/tr}}
            </button>
          {{/if}}
          
          <i class="fa fa-times fa-lg" onclick="toggleGroupLabelEdit($(this).up('li').down('a')); return false;"
             title="{{tr}}Close{{/tr}}"></i>
        </form>
      </a>
    </li>
  {{/foreach}}
  
  {{* create a new group *}}
  <li style="white-space: nowrap;">
    <form name="create-field-group" action="?" method="post" onsubmit="return onSubmitFormAjax(this, {onComplete: ExClass.edit.curry({{$object->_id}})})">
      <input type="hidden" name="m" value="system" />
      <input type="hidden" name="@class" value="CExClassFieldGroup" />
      <input type="hidden" name="ex_class_id" value="{{$object->_id}}" />
      
      <button class="add me-tertiary" type="button" style="margin: -3px;"
              onclick="$(this).hide().next('span').show(); $(this.form.elements.name).tryFocus()">
        {{tr}}CExClassFieldGroup-title-create{{/tr}}
      </button>
      
      <span style="display: none;">
        <button class="submit notext" type="submit" style="margin: -2px"></button>
        {{mb_field class=CExClassFieldGroup field=name size=10 style="margin-right: 4px;"}}
      </span>
    </form>
  </li>
</ul>

<table class="main layout">
  <tr>
    <td style="width: 22em; max-width: 300px;">
      {{foreach from=$object->_ref_groups item=_group}}
        <div id="group-{{$_group->_guid}}" class="me-padding-0 me-no-border" style="display: none;">
          <script type="text/javascript">
            Main.add(function(){
              Control.Tabs.create("ExClassFieldGroup-{{$_group->_guid}}-items", true);
            });
          </script>

          <ul class="control_tabs small" id="ExClassFieldGroup-{{$_group->_guid}}-items" style="white-space: nowrap;">
            <li style="margin-right: 0;">
              <a href="#list-fields-{{$_group->_guid}}" class="active" style="padding: 2px;">
                {{tr}}CExClassFieldGroup-back-class_fields{{/tr}} <small>({{$_group->_ref_fields|@count}})</small>
              </a>
            </li>
            <li style="margin-right: 0;">
              <a href="#list-messages-{{$_group->_guid}}" style="padding: 2px;">
                {{tr}}CExClassFieldGroup-back-class_messages{{/tr}} <small>({{$_group->_ref_messages|@count}})</small>
              </a>
            </li>
            {{if $object->pixel_positionning}}
              <li style="margin-right: 0;">
                <a href="#list-subgroups-{{$_group->_guid}}" style="padding: 2px;">
                  {{tr}}CExClassFieldGroup-back-subgroups{{/tr}} <small>({{$_group->_ref_subgroups|@count}})</small>
                </a>
              </li>
              <li style="margin-right: 0;">
                <a href="#list-pictures-{{$_group->_guid}}" style="padding: 2px;">
                  {{tr}}CExClassFieldGroup-back-class_pictures{{/tr}} <small>({{$_group->_ref_pictures|@count}})</small>
                </a>
              </li>
            {{/if}}
          </ul>

          <table class="main tbl me-no-align me-no-box-shadow">
            <tbody id="list-fields-{{$_group->_guid}}" style="display: none;">
              <tr>
                <td style="text-align: right; min-width: 15em;" colspan="2">
                  <button type="button" class="new" onclick="ExField.create({{$object->_id}}, '{{$_group->_id}}')">
                    {{tr}}CExClassField-title-create{{/tr}}
                  </button>
                </td>
              </tr>
              {{foreach from=$_group->_ref_fields item=_field}}
                <tr class="ex-class-field {{if $_field->disabled}}opacity-30{{/if}}" data-ex_class_field_id="{{$_field->_id}}">
                  <td class="text">
                    {{if $_field->tab_index != null}}
                      <span style="float: left; padding-right: 3px; color: #669966;" title="{{tr}}CExClassField-tab_index{{/tr}}">
                      <small>{{$_field->tab_index}}</small>
                    </span>
                    {{/if}}

                    <span style="float: right;">
                      {{if $_field->report_class}}
                        <img src="./images/icons/reported.png" 
                             title="{{tr}}CExClassField-report_class{{/tr}} ({{tr}}{{$_field->report_class}}{{/tr}})"/>
                      {{/if}}

                      {{if $_field->formula}}
                        <img src="style/mediboard_ext/images/buttons/formula.png"
                             {{if array_key_exists($_field->_id,$object->_wrong_formulas)}} 
                               style="background:red; padding: 2px;" title="Formule invalide: contient un champ inexistant ou désactivé" 
                             {{/if}} />
                      
                        {{if $_field->result_threshold_low}}
                          {{mb_include module=forms template=inc_ex_field_threshold threshold=low value=$_field->result_threshold_low}}
                        {{/if}}
                      
                        {{if $_field->result_threshold_high}}
                          {{mb_include module=forms template=inc_ex_field_threshold threshold=high value=$_field->result_threshold_high}}
                        {{/if}}
                      {{/if}}

                      {{assign var=group_name value=$_group->name}}
                      {{assign var=field_name value=$_field->name}}
                      {{assign var=field_error value=0}}
                      {{if array_key_exists($group_name, $check_fields) && array_key_exists($field_name, $check_fields.$group_name) && !$check_fields.$group_name.$field_name}}
                        {{assign var=field_error value=1}}
                        <img src="./images/icons/no.png" title="{{tr}}common-error-Field not exists in base{{/tr}}"/>
                      {{/if}}

                      {{if $_field->hidden}}
                        <img src="./images/icons/hidden.png" title="{{tr}}CExClassField-hidden{{/tr}}"/>
                      {{elseif $_field->predicate_id}}
                        <img src="./images/icons/showhide.png" title="{{tr}}CExClassField-predicate_id{{/tr}}"/>
                      {{/if}}

                      {{assign var=_spec_type value=$_field->_spec_object->getSpecType()}}
                      {{assign var=_can_formula_arithmetic value='Ox\Mediboard\System\Forms\CExClassField::formulaCanArithmetic'|static_call:$_spec_type}}
                      {{assign var=_can_formula_concat value='Ox\Mediboard\System\Forms\CExClassField::formulaCanConcat'|static_call:$_spec_type}}

                      {{if $_can_formula_arithmetic || $_can_formula_concat}}
                      <button class="right notext insert-formula {{if $_can_formula_arithmetic}}arithmetic{{/if}} {{if $_can_formula_concat}}concat{{/if}}" 
                              style="margin: -3px; margin-left: -1px; display: none;"
                              onclick="ExFormula.insertText('[{{$_field->_locale|smarty:nodefaults|JSAttribute}}]')">
                        {{tr}}CExClassField.add_to_formula{{/tr}}
                      </button>
                      {{/if}}
                    </span>

                    <a href="#1" onclick="ExField.edit('{{$_field->_id}}', null, null, '{{$_group->_id}}', '{{$field_error}}'); return false;">
                      {{if $_field->_locale}}
                        {{$_field->_locale}}
                      {{else}}
                        [{{$_field->name}}]
                      {{/if}}
                    </a>
                  </td>
                  <td class="narrow spec-type spec-type-{{$_spec_type}}" title="{{tr}}CMbFieldSpec.type.{{$_spec_type}}{{/tr}}" style="padding: 1px;"></td>
                </tr>
              {{foreachelse}}
                <tr>
                  <td colspan="2" class="empty">{{tr}}CExClassField.none{{/tr}}</td>
                </tr>
              {{/foreach}}
            </tbody>

            <tbody id="list-messages-{{$_group->_guid}}" style="display: none;">
              <tr>
                <td style="text-align: right;">
                  <button type="button" class="new" onclick="ExMessage.create('{{$_group->_id}}')">
                    {{tr}}CExClassMessage-title-create{{/tr}}
                  </button>
                </td>
              </tr>
              {{foreach from=$_group->_ref_messages item=_message}}
                <tr class="ex-class-message" data-ex_class_message_id="{{$_message->_id}}">
                  <td class="text">
                    {{if $_message->tab_index != null}}
                      <span style="float: left; padding-right: 3px; color: #669966;" title="{{tr}}CExClassMessage-tab_index{{/tr}}">
                        <small>{{$_message->tab_index}}</small>
                      </span>
                    {{/if}}

                    <span style="float: right;">
                      {{if $_message->predicate_id}}
                        <img src="./images/icons/showhide.png" title="{{tr}}CExClassField-predicate_id{{/tr}}"/>
                      {{/if}}
                    </span>
                    <a href="#1" onclick="ExMessage.edit('{{$_message->_id}}', '{{$_group->_id}}'); return false;">
                      {{$_message}}
                    </a>
                  </td>
                </tr>
              {{foreachelse}}
                <tr>
                  <td class="empty">{{tr}}CExClassMessage.none{{/tr}}</td>
                </tr>
              {{/foreach}}
            </tbody>

            <tbody id="list-subgroups-{{$_group->_guid}}" style="display: none;">
              <tr>
                <td style="text-align: right;" colspan="2">
                  <button type="button" class="new" onclick="ExSubgroup.create('{{$_group->_id}}')">
                    {{tr}}CExClassSubgroup-title-create{{/tr}}
                  </button>
                </td>
              </tr>
              {{foreach from=$_group->_ref_subgroups item=_subgroup}}
                <tr class="ex-class-subgroup" data-ex_subgroup_id="{{$_subgroup->_id}}">
                  <td class="text">
                    <span style="float: right;">
                      {{if $_subgroup->predicate_id}}
                        <img src="./images/icons/showhide.png" title="{{tr}}CExClassField-predicate_id{{/tr}}"/>
                      {{/if}}
                    </span>
                    <a href="#1" onclick="ExSubgroup.edit('{{$_subgroup->_id}}', '{{$_group->_id}}'); return false;">
                      {{$_subgroup}}
                    </a>
                  </td>
                  <td>
                    {{$_subgroup->_count.subgroups+$_subgroup->_count.children_fields+$_subgroup->_count.children_messages}}
                  </td>
                </tr>
              {{foreachelse}}
                <tr>
                  <td colspan="2" class="empty">{{tr}}CExClassFieldSubgroup.none{{/tr}}</td>
                </tr>
              {{/foreach}}
            </tbody>

            <tbody id="list-pictures-{{$_group->_guid}}" style="display: none;">
            <tr>
              <td style="text-align: right;" colspan="2">
                <button type="button" class="new" onclick="ExPicture.create('{{$_group->_id}}')">
                  {{tr}}CExClassPicture-title-create{{/tr}}
                </button>
              </td>
            </tr>
            {{foreach from=$_group->_ref_pictures item=_picture}}
              <tr class="ex-class-picture {{if $_picture->disabled}}opacity-30{{/if}}" data-ex_picture_id="{{$_picture->_id}}">
                <td class="text">
                  <span style="float: right;">
                    {{if $_picture->report_class}}
                      <img src="./images/icons/reported.png" title="{{tr}}CExClassField-report_class{{/tr}} ({{tr}}{{$_picture->report_class}}{{/tr}})"/>
                    {{/if}}
                    {{if $_picture->predicate_id}}
                      <img src="./images/icons/showhide.png" title="{{tr}}CExClassField-predicate_id{{/tr}}"/>
                    {{/if}}
                    {{if $_picture->triggered_ex_class_id}}
                      <i class="fa fa-bolt" title="{{$_picture->_ref_triggered_ex_class}}"></i>
                    {{/if}}
                    {{if $_picture->movable}}
                      <i class="fa fa-arrows"></i>
                    {{/if}}
                    {{if $_picture->drawable}}
                      <i class="fas fa-pencil-alt"></i>
                    {{/if}}
                  </span>
                  <a href="#1" onclick="ExPicture.edit('{{$_picture->_id}}', '{{$_group->_id}}'); return false;">
                    {{$_picture}}
                  </a>
                </td>
                <td class="narrow button" style="height: 48px;">
                  {{if $_picture->_ref_file && $_picture->_ref_file->_id}}
                    {{thumbnail document=$_picture->_ref_file profile=small style="max-width: 48px;"}}
                  {{/if}}
                </td>
              </tr>
              {{foreachelse}}
              <tr>
                <td colspan="2" class="empty">{{tr}}CExClassPicture.none{{/tr}}</td>
              </tr>
            {{/foreach}}
            </tbody>
          </table>
        </div>
      {{/foreach}}
    </td>
    <td id="exFieldEditor" style="width: auto;">
      <!-- exFieldEditor -->&nbsp;
    </td>
  </tr>
</table>

</div>

<div id="fields-layout" class="me-align-auto me-padding-0" style="display: none;">
  {{if $object->pixel_positionning}}
    {{mb_include module=forms template=inc_edit_fields_pixel_layout ex_class=$object grid=$object->_grid out_of_grid=$object->_out_of_grid}}
  {{else}}
    {{mb_include module=forms template=inc_edit_fields_layout ex_class=$object grid=$object->_grid out_of_grid=$object->_out_of_grid}}
  {{/if}}
</div>

<div id="fields-events" class="me-align-auto me-padding-0 tab-container" style="display: none;">
  {{mb_include module=forms template=inc_edit_class_events ex_class=$object}}
</div>

<div id="exclass-notifications" class="me-align-auto me-padding-0 tab-container" style="display: none;"></div>

{{/if}}
