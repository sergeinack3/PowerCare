{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{unique_id var=uid}}

<script type="text/javascript">
updateObjectTree = function(inherit, uid) {
  if (!inherit) {
    return;
  }

  var url = new Url("system", "ajax_configuration_object_tree");
  url.addParam("inherit", inherit);
  url.addParam("uid", uid);
  url.addParam("mode", $V($('config-mode-' + uid)));
  url.requestUpdate("object_guid-selector-container-"+uid);
};

editObjectConfig = function(object_guid, uid) {
  var url = new Url("system", "ajax_edit_object_config");
  url.addParam("object_guid", object_guid);
  url.addParam("module", "{{$module}}");
  url.addParam("uid", uid);
  url.addParam("inherit", $V($("inherit-"+uid)));
  url.addParam('mode', $V($('config-mode-' + uid)));
  url.requestUpdate("object-config-editor-"+uid);
};

toggleAlternativeMode = function(mode) {
  $V($('config-mode-{{$uid}}'), mode);
  updateObjectTree($V($("inherit-{{$uid}}")), "{{$uid}}");
};

viewObjectConfigHistory = function(feature, object_class, object_id) {
  var url = new Url("system", "view_config_history");
  url.addParam("feature", feature);
  url.addParam("object_class", object_class);
  url.addParam("object_id", object_id);
  url.pop(800, 600);
};

Main.add(function(){
  updateObjectTree($V($("inherit-{{$uid}}")), "{{$uid}}");
});
</script>

<input type="hidden" name="config-mode" id="config-mode-{{$uid}}" value="{{$mode}}" />

<table class="main form">
  <tr>
    {{me_form_field nb_cells=2 label=common-Schema class=narrow}}
      <select id="inherit-{{$uid}}" onchange="updateObjectTree($V(this), '{{$uid}}')">
        <option value="static">
          {{tr}}config-inherit-static{{/tr}}
        </option>

        {{foreach name=inherit_list from=$all_inherits item=_inherit}}
          {{if $inherit|@count == 0 || in_array($_inherit, $inherit)}}
            <option {{if $smarty.foreach.inherit_list.first}} selected{{/if}} value="{{$_inherit}}">
              {{tr}}config-inherit-{{$_inherit}}{{/tr}}
            </option>
          {{/if}}
        {{/foreach}}
      </select>
    {{/me_form_field}}

    <th class="narrow ">
      <div class="me-display-none">
        Contexte
      </div>
    </th>
    <td class="narrow" id="object_guid-selector-container-{{$uid}}"></td>
    <td></td>
  </tr>
</table>

<div id="object-config-editor-{{$uid}}"></div>
