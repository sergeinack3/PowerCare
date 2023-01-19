{{*
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  function refreshConfigClasses() {
    var url = new Url("system", "ajax_config_classes");
    url.addParam("module", "{{$m}}");
    url.requestUpdate("object-config");
  }

  Main.add(Control.Tabs.create.curry('tabs-configure', true));
</script>

<ul id="tabs-configure" class="control_tabs">
  <li><a href="#CGroup">{{tr}}CGroups{{/tr}}</a></li>
  <li onmousedown="refreshConfigClasses();">
    <a href="#object-config">{{tr}}config-dPetablissement-object-config{{/tr}}</a>
  </li>
  <li>
    <a href="#CEtabExterne-import-export">{{tr}}Imports/Exports{{/tr}} {{tr}}CEtabExterne{{/tr}}</a>
  </li>
  <li><a href="#config-sae-base">{{tr}}config-sae-base{{/tr}}</a></li>
</ul>

<div id="CGroup" style="display: none;">
  {{mb_include module=etablissement template=CGroup_configure}}
</div>

<div id="object-config" style="display: none;">
  <div class="small-info">{{tr}}config-dPetablissement-object-config-classes{{/tr}}</div>
</div>

<div id="CEtabExterne-import-export" style="display: none;">
  {{mb_include module=etablissement template=inc_import_etab_externe}}
</div>

<div id="config-sae-base" style="display: none;">
  {{mb_include template=inc_config_base_sae}}
</div>
