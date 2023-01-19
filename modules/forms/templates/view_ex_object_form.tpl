{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{unique_id var=ex_form_hash}}
{{assign var=ex_form_hash value="ex_$ex_form_hash"}}

{{assign var=_verified value="n/a"}}
{{if $ex_object->_verified}}
  {{assign var=_verified value=$ex_object->_verified}}
{{/if}}

{{assign var=grid_colspan value=$ex_object->_ref_ex_class->getGridWidth()}}

{{if !@$readonly}}

<script type="text/javascript">
ExObjectForms = window.ExObjectForms || {};

ExObjectForms.{{$ex_form_hash}} = {
  confirmSavePrint: function(form){
    var oldCallback = $V(form.callback);
    $V(form.callback, 'ExObjectForms.{{$ex_form_hash}}.printForm');

    if (FormObserver.changes > 0) {
      Modal.confirm("Pour imprimer le formulaire, il est nécessaire de l'enregistrer, souhaitez-vous continuer ?", {
        onOK: function(){
          form.onsubmit();
          $V(form.callback, oldCallback);
        }
      });
    }
    else {
      form.onsubmit();
      $V(form.callback, oldCallback);
    }

    return false;
  },

  closeOnSuccess: function(id, obj) {
    this.updateId(id, obj);

    if (!(obj._ui_messages[3] || obj._ui_messages[4])) { // warning ou error
      var element_id = "{{$_element_id}}";

      if (element_id && window.opener && !window.opener.closed && window.opener !== window && window.opener.ExObject) {
        if (element_id.charAt(0) === "@") {
          eval("window.opener."+element_id.substr(1)+"()"); // ARG
        }
        else {
          var target = window.opener.$(element_id);

          if (target.get("ex_class_id")) {
            window.opener.ExObject.loadExObjects.defer(
              target.get("reference_class"),
              target.get("reference_id"),
              element_id,
              target.get("detail"),
              target.get("ex_class_id")
            );
          }
          else {
            window.opener.ExObject.register.defer(element_id, {
              ex_class_id: "{{$ex_class_id}}",
              object_guid: "{{$object_guid}}",
              event_name: "{{$event_name}}",
              _element_id: element_id
            });
          }
        }
      }

      if (window.opener && window.opener.ExObject) {
        {{if $form_name && !$ex_object->_id}}
          window.opener.ExObject.addToForm("{{$form_name}}", "CExObject_{{$ex_class_id}}-"+id);
        {{/if}}

        if (window.opener.ExObject.onAfterSave) {
          window.opener.ExObject.onAfterSave('{{$memo}}', id, obj);
        }
      }

      {{if $ajax}}
        getForm("editExObject_{{$ex_form_hash}}").up(".form-ajax-container").fire("form:submitted");
      {{else}}
        // Defer close because of IE9 and protocole_to_concepts which calls "window.opener.ExObject.checkOpsBeforeProtocole"
        if (document.documentMode && document.documentMode >= 9) {
          window.blur();

          (function(){
            window.close();
          }).delay(5);
        }
        else {
          (function(){
            window.close();
          }).defer();
        }
      {{/if}}
    }
  },

  printForm: function(id, obj) {
    this.updateId(id, obj);

    FormObserver.changes = 0;
    var iFrame = $("printIframe");
    iFrame.src = "about:blank";
    iFrame.src = "?{{$smarty.server.QUERY_STRING|html_entity_decode}}&readonly=1&print=1&autoprint=1&ex_object_id="+id;
  },

  updateId: function(id, obj) {
    if (ExObjectForms.{{$ex_form_hash}} && ExObjectForms.{{$ex_form_hash}}.formObserver) {
      ExObjectForms.{{$ex_form_hash}}.formObserver.stop();
    }

    $V(getForm("editExObject_{{$ex_form_hash}}").ex_object_id, id);
    {{*(window.callback_{{$ex_class_id}} || window.launcher && window.launcher.callback_{{$ex_class_id}})();*}}
  }
};

Main.add(function(){
  var form = getForm("editExObject_{{$ex_form_hash}}");

  ExObject.current = {object_guid: "{{$object_guid}}", event_name: "{{$event_name}}"};
  ExObject.pixelPositionning = {{$ex_object->_ref_ex_class->pixel_positionning}} == 1;
  new ExObjectFormula({{$formula_token_values|@json}}, form);
  ExObject.initPredicates({{$ex_object->_fields_default_properties|@json:true}}, {{$ex_object->_fields_display_struct|@json:true}}, form);

  // Check the configuration to check for modifications before closing the form
  {{if $conf.forms.CExClass.check_modification_before_close}}
    // The form watcher : the same as in forms.js, but delayed, because we have deferred actions in initPredicates, which may occur before
    (function(){
      ExObjectForms.{{$ex_form_hash}}.formObserver = new Form.Observer(
        form,
        0.5,
        function(form, value) { FormObserver.elementChanged(form, value); }
      );
    }).delay(1);
  {{/if}}
});
</script>

{{if !$print && !$preview_mode}}
  <iframe id="printIframe" style="position: absolute; width:0; height:0; border:0;"></iframe>

  <span style="float: right;">


    {{if $ajax}}
      {{if $ex_object->_id}}
        <button type="button" class="print singleclick" onclick="ExObject.print('{{$ex_object->_id}}', '{{$ex_object->_ex_class_id}}', '{{$object_guid}}')">
            {{tr}}Print{{/tr}}
          </button>
      {{/if}}
      {{else}}
        <button type="button" class="print singleclick" onclick="ExObjectForms.{{$ex_form_hash}}.confirmSavePrint(getForm('editExObject_{{$ex_form_hash}}'))">
          {{tr}}Print{{/tr}}
        </button>
    {{/if}}
    </span>
{{/if}}

{{if !$noheader}}
  <h2 style="font-weight: bold;">
    {{mb_include module=forms template=inc_ex_form_header}}
  </h2>
{{/if}}

<script type="text/javascript">
  Main.add(function(){
    Control.Tabs.create("ex_class-groups-tabs-{{$ex_form_hash}}", false, {
      afterChange: function(container){
        if (Object.isFunction(ExObject.groupTabsCallback[container.id])) {
          ExObject.groupTabsCallback[container.id]();
        }

        $$(".pixel-grid-widgets").invoke("hide");
        var widgets = $(container.id+"-widgets");
        if (widgets) {
          widgets.show();
        }
      }
    });

    {{if !$ajax}}
    if (window.parent != window || window.opener != window) {
      document.title = "{{$ex_object->_ref_ex_class->name}} - {{$object}}".htmlDecode();
    }
    {{/if}}
  });
</script>

{{if $ex_object->_quick_access_creation}}
  <div class="small-info">L'enregistrement de ce formulaire créera un élement de type
    <strong>{{tr}}{{$ex_object->_quick_access_creation}}{{/tr}}</strong> lié à
    <strong>{{tr}}{{$object->_class}}{{/tr}}</strong>
    <strong onmouseover="ObjectTooltip.createEx(this,'{{$object->_guid}}')">
      {{$object}}
    </strong>.
  </div>
{{/if}}

{{$ui_msg|smarty:nodefaults}}

<div class="form-wrapper">
  {{mb_form name="editExObject_$ex_form_hash" m="system" dosql="do_ex_object_aed" method="post"
            onsubmit="ExObject.getPicturesData(this); return onSubmitFormAjax(this)"}}
    {{mb_key object=$ex_object}}
    {{mb_field object=$ex_object field=_ex_class_id hidden=true}}
    {{mb_field object=$ex_object field=_event_name hidden=true}}
    {{mb_field object=$ex_object field=group_id hidden=true}}
    {{mb_field object=$ex_object field=_pictures_data hidden=true}}
    {{mb_field object=$ex_object field=_quick_access_creation hidden=true}}
    {{mb_field object=$ex_object field=_hidden_fields hidden=true}}

    {{if !$ex_object->_id}}
      {{mb_field object=$ex_object field=object_class hidden=true}}
      {{mb_field object=$ex_object field=object_id hidden=true}}

      {{mb_field object=$ex_object field=reference_class hidden=true}}
      {{mb_field object=$ex_object field=reference_id hidden=true}}

      {{mb_field object=$ex_object field=reference2_class hidden=true}}
      {{mb_field object=$ex_object field=reference2_id hidden=true}}
    {{/if}}

    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="callback" value="ExObjectForms.{{$ex_form_hash}}.closeOnSuccess" />

    <ul id="ex_class-groups-tabs-{{$ex_form_hash}}" class="control_tabs me-control-tabs-wraped" style="clear: left;">
      {{foreach from=$groups item=_group}}
        {{if (is_countable($_group->_ref_fields) && $_group->_ref_fields|@count) ||
             (is_countable($_group->_ref_messages) && $_group->_ref_messages|@count) ||
             (is_countable($_group->_ref_host_fields) && $_group->_ref_host_fields|@count) ||
             (is_countable($_group->_ref_subgroups) && $_group->_ref_subgroups|@count) ||
             (is_countable($_group->_ref_pictures) && $_group->_ref_pictures|@count) ||
             (is_countable($_group->_ref_widgets) && $_group->_ref_widgets|@count)
        }}
        <li>
          <a href="#tab-{{$_group->_guid}}">{{$_group}}</a>
        </li>
        {{/if}}
      {{/foreach}}

      {{foreach from=$ex_object->_native_views item=_object key=_name}}
        {{if $_object && $_object->_id || $preview_mode}}
          <li><a href="#tab-native_views-{{$_name}}-tab" class="special">{{tr}}CExClass.native_views.{{$_name}}{{/tr}}</a></li>
        {{/if}}
      {{/foreach}}

      {{if $ex_object->_ref_ex_class->pixel_positionning}}
        <li style="padding-left: 3em;">
          <button class="modify singleclick" type="submit" {{if !$object->_id}}disabled{{/if}}>{{tr}}Save{{/tr}}</button>

          {{if $can_delete && $ex_object->_id}}
            <button type="button" class="trash" onclick="confirmDeletion(this.form,{callback: (function(){ FormObserver.changes = 0; onSubmitFormAjax(this.form); }).bind(this), typeName:'', objName:'{{$ex_object->_view|smarty:nodefaults|JSAttribute}}'})">
              {{tr}}Delete{{/tr}}
            </button>
          {{/if}}
        </li>
      {{/if}}
    </ul>

    {{if $ex_object->_ref_ex_class->pixel_positionning}}
      {{mb_include module=forms template=inc_form_pixel_grid}}
    {{else}}
      {{mb_include module=forms template=inc_form_grid}}
    {{/if}}

  {{/mb_form}}

  {{* WIDGETS MUST BE OUT OF THE <form> BECAUSE THEY MAY CONTAIN ONE *}}
  {{if $ex_object->_ref_ex_class->pixel_positionning}}
    <div class="pixel-positionning pixel-positionning-widgets">
    {{foreach from=$groups item=_group}}
      <div id="tab-{{$_group->_guid}}-widgets" style="position: relative; display: none;" class="pixel-grid group-layout pixel-grid-widgets">
        {{* ----- SUB GROUPS ----- *}}
        {{foreach from=$_group->_ref_subgroups item=_subgroup}}
          {{assign var=_properties value=$_subgroup->_default_properties}}

          {{assign var=_style value=""}}
          {{foreach from=$_properties key=_type item=_value}}
            {{if $_value != ""}}
              {{assign var=_style value="$_style $_type:$_value;"}}
            {{/if}}
          {{/foreach}}

          <div class="resizable subgroup group-layout subgroup-widgets" id="subgroup-{{$_subgroup->_guid}}-widgets"
               style="left:{{$_subgroup->coord_left}}px; top:{{$_subgroup->coord_top}}px; width:{{$_subgroup->coord_width}}px; height:0; min-height: 0;">
            <div {{if !$_subgroup->title}} class="no-label" {{else}} class="with-label" {{/if}} style="{{$_style}}">
              {{* WIDGETS *}}
              {{foreach from=$_subgroup->_ref_children_widgets item=_widget}}
              <div class="{{if $_widget->_no_size}} no-size {{/if}} form-widget"
              id="form-widget-{{$_widget->_guid}}"
              data-ex_class_widget_id="{{$_widget->_id}}"
              style="left:{{$_widget->coord_left}}px; top:{{$_widget->coord_top}}px; width:{{$_widget->coord_width}}px; height:{{$_widget->coord_height}}px;">
              {{assign var=_def value=$_widget->getWidgetDefinition()}}
              {{$_def->display($ex_object)}}
              </div>
              {{/foreach}}
            </div>
          </div>
        {{/foreach}}

        {{* WIDGETS *}}
        {{foreach from=$_group->_ref_root_widgets item=_widget}}
          <div class="{{if $_widget->_no_size}} no-size {{/if}} form-widget"
               id="form-widget-{{$_widget->_guid}}"
               data-ex_class_widget_id="{{$_widget->_id}}"
               style="left:{{$_widget->coord_left}}px; top:{{$_widget->coord_top}}px; width:{{$_widget->coord_width}}px; height:{{$_widget->coord_height}}px;">
            {{assign var=_def value=$_widget->getWidgetDefinition()}}
            {{$_def->display($ex_object)}}
          </div>
        {{/foreach}}
      </div>
    {{/foreach}}
    </div>
  {{/if}}
</div>

{{foreach from=$ex_object->_native_views item=_object key=_name}}
  <div id="tab-native_views-{{$_name}}-tab" style="display: none;">
    {{if $preview_mode}}
      <div class="small-info">
        Ici apparaitra la vue <strong>{{tr}}CExClass.native_views.{{$_name}}{{/tr}}</strong>.
      </div>
    {{else}}
      <div id="tab-native_views-{{$_name}}"></div>
      {{mb_include module=forms template="inc_native_view_$_name" object=$_object}}
    {{/if}}

    {{if !$ex_object->_ref_ex_class->pixel_positionning}}
      <div style="text-align: center">
        <button class="submit singleclick" onclick="getForm('editExObject_{{$ex_form_hash}}').onsubmit()"
                {{if $preview_mode}}disabled{{/if}}>
          Enregistrer le formulaire
        </button>
      </div>
    {{/if}}
  </div>
{{/foreach}}

{{else}}

{{* ----   READONLY   ---- *}}

<script type="text/javascript">
Main.add(function(){
  {{if !$ajax}}
    document.title = "{{$ex_object->_ref_ex_class->name}} - {{$object}}".htmlDecode();
  {{/if}}

  // Agrandissement des pages en fonction des élements positionnés
  var pages = $$(".pixel-grid-print");
  pages.each(function(page){
    var pageDim = page.getDimensions();
    var width = pageDim.width;
    var height = pageDim.height;
    var items = page.descendants();

    items.each(function(item){
      var dim = item.getDimensions();
      var pos = {top: parseInt(item.style.top || 0), left: parseInt(item.style.left || 0)};
      height = Math.max(height, dim.height + pos.top);
      width  = Math.max(width,  dim.width  + pos.left);
    });

    page.style.height = height+"px";
    page.style.width = width+"px";
  });

  var form = getForm("editExObject_{{$ex_form_hash}}");
  ExObject.initPredicates({{$ex_object->_fields_default_properties|@json:true}}, {{$ex_object->_fields_display_struct|@json:true}}, form);

  {{if $autoprint}}
    if (document.documentMode && document.execCommand) {
      window.focus();
      document.execCommand('print', false, null);
    }
    else {
      window.print();
    }
  {{/if}}
});

function switchMode(){
  var only_filled = Url.parse().query.toQueryParams().only_filled;
  location.href = location.href.replace('only_filled='+only_filled, 'only_filled='+(only_filled == 1 ? 0 : 1));
}
</script>

{{* form used for predicates *}}
<form name="editExObject_{{$ex_form_hash}}" onsubmit="return false" method="get" style="display: none;">
  {{mb_field object=$ex_object field=_hidden_fields hidden=true}}

  {{foreach from=$groups key=_group_id item=_group}}
    {{foreach from=$_group->_ref_fields item=_field}}
      {{mb_field object=$ex_object field=$_field->name hidden=true}}
    {{/foreach}}
  {{/foreach}}
</form>

{{if $print}}
  <div style="float: right;" class="not-printable">
    {{tr}}common-action-Display{{/tr}} :

    {{foreach from='Ox\Mediboard\System\Forms\CExClass'|static:'_native_views' key=_name item=_classes}}
      <label>
        <input type="checkbox" name="show_native_view[]" value="{{$_name}}"
                {{if array_key_exists($_name, $ex_object->_native_views)}} checked {{else}} disabled {{/if}}
          onclick="$('ex-object-native-view-{{$_name}}').toggle();"
        />
          {{tr}}CExClass.native_views.{{$_name}}{{/tr}}
      </label>
    {{/foreach}}

    <button class="change" onclick="switchMode()">
      Tous les champs
    </button>
    <button class="print singleclick" onclick="window.print()">{{tr}}Print{{/tr}}</button>
  </div>
{{/if}}

{{if $print}}
  {{mb_include style=mediboard_ext template=open_printable}}
{{/if}}

<table class="main {{if $print && !$ex_object->_ref_ex_class->pixel_positionning}} print {{/if}}">
  {{if !$noheader}}
  <thead>
    <tr>
      <td colspan="{{$grid_colspan}}">
        <p style="font-weight: bold; font-size: 1.1em;">
          {{mb_include module=forms template=inc_ex_form_header readonly=true}}
        </p>
        <hr style="border-color: #333; margin: 4px 0;" />
      </td>
    </tr>
  </thead>
  {{/if}}

  {{if $ex_object->_ref_ex_class->pixel_positionning && !$only_filled}}
    <tr>
      <td colspan="4">
        {{mb_include module=forms template=inc_form_pixel_grid}}
      </td>
    </tr>
  {{else}}

  {{if $only_filled}}

    <tr>
      <td colspan="{{$grid_colspan}}">
        {{mb_include module=forms template=inc_vw_ex_object ex_object=$ex_object}}
      </td>
    </tr>

    {{foreach from=$ex_object->_native_views key=_name item=_object}}
      <tr id="ex-object-native-view-{{$_name}}">
        <td colspan="{{$grid_colspan}}">
          <h4 style="margin: 0.5em; border-bottom: 1px solid #666;">{{tr}}CExClass.native_views.{{$_name}}{{/tr}}</h4>
            {{mb_include module=forms template="inc_native_view_`$_name`_print" object=$_object}}
        </td>
      </tr>
    {{/foreach}}

  {{else}}

    {{foreach from=$grid key=_group_id item=_grid}}
    <tbody id="tab-{{$groups.$_group_id->_guid}}">
      <tr>
        <th class="title" colspan="{{$grid_colspan}}">{{$groups.$_group_id}}</th>
      </tr>

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
                  <th style="font-weight: bold; vertical-align: middle; white-space: normal;">
                    <div class="field-{{$_field->name}} field-label">
                      {{mb_label object=$ex_object field=$_field_name}}
                    </div>
                  </th>
                {{else}}
                  <td style="font-weight: bold; text-align: left;">
                    <div class="field-{{$_field->name}} field-label">
                      {{mb_label object=$ex_object field=$_field_name}}
                    </div>
                  </td>
                {{/if}}
              {{elseif $_group.type == "field"}}
                <td class="text">
                  <div class="field-{{$_field->name}} field-input" {{if $ex_object->_specs.$_field_name|instanceof:'Ox\Core\FieldSpecs\CTextSpec'}} class="text-block" {{/if}}>
                    {{$_field->prefix}}
                    {{mb_value object=$ex_object field=$_field_name}}
                    {{$_field->suffix}}
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
                <th style="font-weight: bold; vertical-align: top; white-space: normal;">
                  {{mb_label object=$_host_field->_ref_host_object field=$_host_field->_field}}
                </th>
              {{else}}
                <td style="font-weight: bold; text-align: left; white-space: normal;">
                  {{mb_label object=$_host_field->_ref_host_object field=$_host_field->_field}}
                </td>
              {{/if}}
            {{else}}
              <td class="text">
                {{mb_value object=$_host_field->_ref_host_object field=$_host_field->_field}}
              </td>
            {{/if}}
          {{else}}
            {{assign var=_message value=$_group.object}}
              {{if $_group.type == "message_title"}}

                {{if $_message->coord_text_x == $_message->coord_title_x+1}}
                  <th style="font-weight: bold; vertical-align: middle; white-space: normal;">
                    {{$_message->title}}
                  </th>
                {{else}}
                  <td style="font-weight: bold; text-align: left;">
                    {{$_message->title}}
                  </td>
                {{/if}}
              {{else}}
                <td class="text">
                  <div class="CExClassMessage-container" id="message-{{$_message->_guid}}">
                    {{mb_include module=forms template=inc_ex_message}}
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
          <th style="font-weight: bold; width: 50%; vertical-align: middle; white-space: normal;" colspan="2">
            <div class="field-{{$_field->name}} field-label">
              {{mb_label object=$ex_object field=$_field_name}}
            </div>
          </th>
          <td colspan="2" class="text">
            <div class="field-{{$_field->name}} field-label" {{if $ex_object->_specs.$_field_name|instanceof:'Ox\Core\FieldSpecs\CTextSpec'}} class="text-block" {{/if}}>
              {{$_field->prefix}}
              {{mb_value object=$ex_object field=$_field_name}}
              {{$_field->suffix}}
            </div>
          </td>
        </tr>
      {{/if}}
    {{/foreach}}

    </tbody>
    {{/foreach}}

      {{foreach from=$ex_object->_native_views key=_name item=_object}}
          {{if $_object && $_object->_id}}
            <tbody id="ex-object-native-view-{{$_name}}">
              <tr>
                <th class="title" colspan="{{$grid_colspan}}">
                    {{tr}}CExClass.native_views.{{$_name}}{{/tr}}
                </th>
              </tr>

              <tr>
                <td colspan="{{$grid_colspan}}">
                    {{mb_include module=forms template="inc_native_view_`$_name`_print" object=$_object}}
                </td>
              </tr>
            </tbody>
          {{/if}}
      {{/foreach}}

  {{/if}}

  {{/if}}

</table>

{{if $print}}
  {{mb_include style=mediboard_ext template=close_printable}}
{{/if}}

{{/if}}
