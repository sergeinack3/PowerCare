{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=eai script=interop_actor}}

{{if !$actor->_id}}
  <div class="small-error">{{tr}}CInteropActor-msg-None actor{{/tr}}</div>
  {{mb_return}}
{{/if}}

{{if $object && $object->_id}}
  <script>
    document.getElementById("menu_configs_receiver").setAttribute("class", "special active");
  </script>
{{/if}}

{{if $actor->_id && $object}}
  <script>
    Main.add(function () {
      Control.Tabs.create('tabs-object-config-{{$object->object_id}}-{{$object->_id}}', true);
    });
    importConfig = function(format_config_guid, actor_guid) {
      var url = new Url("eai", "import_config");
      url.addParam("format_config_guid", format_config_guid);
      url.addParam("actor_guid"        , actor_guid);
      url.popup(800, 600, "Import config XML");
      return false;
    }
  </script>
{{/if}}

{{mb_include module=eai template=inc_summary_actor}}

{{if $object && $object->_id}}
  <a class="button download" target="_blank" href="?m=eai&raw=export_config&config_guid={{$object->_guid}}">
    {{tr}}Export{{/tr}}
  </a>

  <button class="upload" onclick="importConfig('{{$object->_guid}}', '{{$actor->_guid}}');">
    {{tr}}Import{{/tr}}
  </button>
{{/if}}

<div id="configuration">
  {{if $object}}
    <form name="editObjectConfig-{{$object->_id}}" method="post"
          onsubmit="return InteropActor.onSubmitObjectConfigs(this, '{{$actor->_guid}}')">
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
                        {{mb_include module=system template=inc_config_object_value _field=$_item_field_name}}
                      {{/foreach}}
                    {{else}}
                      {{mb_include module=system template=inc_config_object_value _field=$_field_name}}
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
  {{/if}}
</div>

<button type="button" class="fa fa-check" style="margin-top: 10px; float: right;"
  onclick="Control.Modal.close();">{{tr}}Finish{{/tr}}
</button>