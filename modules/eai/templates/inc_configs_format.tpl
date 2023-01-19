{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  importConfig = function(format_config_guid, actor_guid) {
    var url = new Url("eai", "import_config");
    url.addParam("format_config_guid", format_config_guid);
    url.addParam("actor_guid"        , actor_guid);
    url.popup(800, 600, "Import config XML");
    return false;
  }
</script>

{{if $format_config->_id}}
  <a class="button download" target="_blank" href="?m=eai&raw=export_config&config_guid={{$format_config->_guid}}">
    {{tr}}Export{{/tr}}
  </a>

  <button class="upload" onclick="importConfig('{{$format_config->_guid}}', '{{$actor->_guid}}');">
    {{tr}}Import{{/tr}}
  </button>
{{/if}}

<table class="form">
  {{mb_include module=system template=inc_form_table_header object=$format_config}}

  {{foreach from=$categories key=cat_name item=_fields}}
    {{if $cat_name}}
      <tr>
        <th colspan="4" class="section" style="text-align: center;">{{$cat_name}}</th>
      </tr>
    {{/if}}

    {{foreach from=$_fields item=_field_name}}
      <tr>
        <th>{{mb_title object=$format_config field=$_field_name}}</th>
        <td>
          <form name="editConfigsFormat-{{$_field_name}}" method="post" onsubmit="return onSubmitFormAjax(this);">
            <input type="hidden" name="sender_id"    value="{{$actor->_id}}" />
            <input type="hidden" name="sender_class" value="{{$actor->_class}}" />
            {{mb_key   object=$format_config}}
            {{mb_class object=$format_config}}
            <input type="hidden" name="callback" value="InteropActor.callbackConfigsFormats" />

            {{mb_field emptyLabel="Choose" object=$format_config field=$_field_name onchange="this.form.onsubmit();"}}
          </form>
        </td>
      </tr>
    {{/foreach}}
  {{/foreach}}
</table>
