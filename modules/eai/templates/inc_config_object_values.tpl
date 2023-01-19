{{*
 * @package Mediboard\eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=hasMultipleFormat value=false}}
{{if $formats|@count > 1}}
    {{assign var=hasMultipleFormat value=true}}
{{/if}}

<script>
  exportConfig = function(object_guid, format) {
    var url = new Url("eai", "exportConfigs", 'raw');
    url.addParam("object_guid", object_guid);
    url.addParam("format", format);
    url.pop();
    setTimeout(() => url.close(), 1300);
  };

  importConfig = function(object_guid) {
    var url = new Url("eai", "showUploadFileConfig");
    url.addParam("object_guid", object_guid);
    url.popup(800, 600, "Import configs");
  };

  viewObjectConfigHistory = function(feature, object_class, object_id) {
    var url = new Url("system", "view_config_history");
    url.addParam("feature", feature);
    url.addParam("object_class", object_class);
    url.addParam("object_id", object_id);
    url.pop(800, 600);
  };

  showOnlyConfigFormat = function(actor_guid, format) {
    var container = document.getElementById('table-edit-config-' + actor_guid);
    var elements = container.select('tr[data-section]');

    for (element of elements) {
      const section_name = element.getAttribute('data-section');
      if (!section_name.endsWith(format)) {
        element.style.display = 'none';
      }
    }
  };

  editObjectConfig = function(object_guid, uid) {
    {{if $hasMultipleFormat}}
      var active_tab = document.getElementById('tabs-configure-format').select('.active')[0];
      var active_format = document.getElementById(active_tab.getAttribute('href').substr(1)).getAttribute('data-format');
      var options = {
        onComplete: function () {
          showOnlyConfigFormat("{{$actor_guid}}", active_format);
        }
      };
      {{else}}
      var active_format = "{{$formats|@first}}";
      var options = {}
    {{/if}}

    var url = new Url("system", "ajax_edit_object_config");
    url.addParam("object_guid", object_guid);
    url.addParam("module", "{{$module}}");
    url.addParam("uid", uid);
    url.addParam("inherit", '{{$object_class}}');
    url.addParam('mode', '{{$mode}}');
    url.requestUpdate("object-config-editor-{{$actor_guid}}-" + active_format, options)
  };

  {{if !$hasMultipleFormat}}
    Main.add(function () {
      editObjectConfig('{{$actor_guid}}', '{{$actor_guid}}')
    });
  {{/if}}
</script>

{{if $hasMultipleFormat}}
  <script>
    Main.add(function () {
      Control.Tabs.create('tabs-configure-format', true, {
        afterChange: function (container) {
            {{foreach from=$formats item=format}}
              document.getElementById("object-config-editor-{{$actor_guid}}-{{$format}}").innerHTML = '';
            {{/foreach}}

          editObjectConfig('{{$actor_guid}}', '{{$actor_guid}}')
        }
      });
    });
  </script>

  <ul id="tabs-configure-format" class="control_tabs">
    {{foreach from=$formats item=format}}
      <li><a href="#container-object-config-editor-{{$actor_guid}}-{{$format}}">{{tr}}CInteropActor-msg-config {{$format}}{{/tr}}</a></li>
    {{/foreach}}
  </ul>
{{/if}}

{{foreach from=$formats item=format}}
  <div id="container-object-config-editor-{{$actor_guid}}-{{$format}}" data-format="{{$format}}">
    <table class="tbl">
      <tr>
        <td>
          <button type="button" class="button download" onclick="exportConfig('{{$actor_guid}}', '{{$format}}')">{{tr}}common-action-Export{{/tr}}</button>
          <button type="button" class="button upload" onclick="importConfig('{{$actor_guid}}')">{{tr}}common-action-Import{{/tr}}</button>
        </td>
      </tr>
    </table>

    <div id="object-config-editor-{{$actor_guid}}-{{$format}}"></div>
  </div>
{{/foreach}}
