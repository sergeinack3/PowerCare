{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=compteRendu script=document}}

{{assign var=object_class value=$object->_class}}
{{assign var=object_id value=$object->_id}}

{{mb_ternary var=tab_name test=$accordDossier value="tab-`$object_class``$object_id`" other="tab-consult"}}

<script>
  ObjectTooltip.modes.locker = {
    module: "compteRendu",
    action: "ajax_show_locker",
    sClass: "tooltip"
  };

  Main.add(function() {
    Control.Tabs.create("{{$tab_name}}", false);
  });
</script>

<ul id="{{$tab_name}}" class="control_tabs me-control-tabs-wraped">
{{foreach from=$affichageFile item=_cat key=_cat_id}}
  {{assign var=docCount value=$_cat.items|@count}}
  {{if $docCount || "dPfiles CFilesCategory show_empty"|gconf || $_cat_id == 0}}
    <li>
      <a href="#Category-{{$_cat_id}}" {{if !$docCount}}class="empty"{{/if}}>
        {{$_cat.name}}
        <small>({{$docCount}})</small>
      </a>
    </li>
  {{/if}}
{{/foreach}}
</ul>

{{foreach from=$affichageFile item=_cat key=_cat_id}}
{{assign var=docCount value=$_cat.items|@count}}

{{if $docCount || "dPfiles CFilesCategory show_empty"|gconf || $_cat_id == 0}}
<table class="tbl me-no-align" id="Category-{{$_cat_id}}" style="display: none;">
  {{if $canFile && !$accordDossier}}
  <tr>
    <td colspan="2" class="text">
      <button class="new" onclick="uploadFile('{{$object->_guid}}', '{{$_cat_id}}')">
        {{tr}}CFile-action-Add{{/tr}}
      </button>
    </td>
  </tr>
  {{/if}}
  <tbody id="Category-{{$_cat_id}}">
    {{mb_include module=files template=inc_list_files list=$_cat.items}}
  </tbody>
</table>
{{/if}}
{{/foreach}}