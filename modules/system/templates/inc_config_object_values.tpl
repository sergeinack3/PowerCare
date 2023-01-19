{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  resetValue = function(object_id, field_name) {
    var oForm = getForm('editObjectConfig-'+object_id);
    $V(oForm[field_name], "");
  };
  
  onSubmitObjectConfigs = function(oForm, object_instance_id, object_guid) {
    return onSubmitFormAjax(oForm, (window.refreshConfigObjectValues || InteropActor.refreshConfigObjectValues).curry(object_instance_id, object_guid));
  };

  importConfig = function(object_config_guid) {
    var url = new Url("system", "import_config");
    url.addParam("object_config_guid", object_config_guid);
    url.popup(800, 600, "Import config XML");

    return false;
  };

  Main.add(function() {
    Control.Tabs.create('tabs-object-config-{{$object->object_id}}-{{$object->_id}}', true);
  });
</script>

{{if $object->_id}}
  <a class="button download" target="_blank" href="?m=system&raw=export_config&object_guid={{$object->_guid}}">
    {{tr}}Export{{/tr}}
  </a>

  <button class="upload" onclick="importConfig('{{$object->_guid}}');">
    {{tr}}Import{{/tr}}
  </button>
{{/if}}

<form name="editObjectConfig-{{$object->_id}}" method="post"
      onsubmit="return onSubmitObjectConfigs(this, '{{$object->object_id}}', '{{$object->_guid}}') ">
  <input type="hidden" name="m" value="system" />
  <input type="hidden" name="del" value="0" /> 

  <input type="hidden" name="@class" value="{{$object->_class}}" /> 
  {{mb_key object=$object}}

  <input type="hidden" name="object_id" value="{{$object->object_id}}" />
  <table class="form">
    <tr>
      <td style="vertical-align: top; width: 15%;">
        <ul id="tabs-object-config-{{$object->object_id}}-{{$object->_id}}" class="control_tabs_vertical small">
          {{foreach from=$categories key=cat_name item=_fields}}
            <li><a href="#object-config-{{$cat_name}}">{{$cat_name}} <small>({{$_fields|@count}})</small></a></li>
          {{/foreach}}
        </ul>
      </td>
      <td style="vertical-align: top;">
        {{foreach from=$categories key=cat_name item=_fields}}
          <div id="object-config-{{$cat_name}}" style="display: none">
            <table class="form">
              {{mb_include module=system template=inc_form_table_header colspan=3}}

              <tr>
                <th class="category">{{tr}}Name{{/tr}}</th>
                <th class="category">{{tr}}Value{{/tr}}</th>
                <th class="category">{{tr}}Default{{/tr}}</th>
              </tr>

              {{foreach from=$_fields key=_field_section_name item=_field_name}}
                {{if is_array($_field_name)}}
                  {{if $_field_section_name}}
                    <tr>
                      <th colspan="4" class="section" style="text-align: center;">{{$_field_section_name}}</th>
                    </tr>
                  {{/if}}

                  {{foreach from=$_field_name item=_item_field_name}}
                    {{mb_include template=inc_config_object_value _field=$_item_field_name}}
                  {{/foreach}}
                {{else}}
                  {{mb_include template=inc_config_object_value _field=$_field_name}}
                {{/if}}
              {{/foreach}}

              <tr>
                <td class="button" colspan="4">
                {{if $object->_id}}
                <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
                <button type="button" class="trash" onclick="confirmDeletion(this.form,{typeName:'',objName:'{{$object->_view|smarty:nodefaults|JSAttribute}}',ajax:true})">
                  {{tr}}Delete{{/tr}}
                </button>
                {{else}}
                <button class="submit singleclick" type="submit">{{tr}}Create{{/tr}}</button>
                {{/if}}
                </td>
              </tr>
             </table>
          </div>
        {{/foreach}}
      </td>
    </tr>
  </table>
</form>