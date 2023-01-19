{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if "OxLaboClient"|module_active && 'Ox\Mediboard\OxLaboClient\OxLaboClient::canShowOxLabo'|static_call:null}}
  {{mb_script module=oxLaboClient script=oxlaboalert ajax=true}}
{{/if}}

{{assign var=consult_anesth value=""}}

{{if $object|instanceof:'Ox\Mediboard\Cabinet\CConsultation'}}
  {{assign var=consult_anesth value=$object->loadRefConsultAnesth()}}
{{/if}}

{{if "dPcabinet CConsultation verification_access"|gconf
  && (($object|instanceof:'Ox\Mediboard\Cabinet\CConsultAnesth' && !$object->sejour_id && !$object->canEdit())
  || ($object|instanceof:'Ox\Mediboard\Cabinet\CConsultation'  && !$object->sejour_id && (!$consult_anesth->_id || !$consult_anesth->sejour_id) && !$object->canEdit()))}}
  <div class="small-info">
    {{tr}}CConsultation-Needs right edit prat{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

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
    Control.Tabs.create("{{$tab_name}}", true);

    var button = $("docItem_{{$object->_guid}}");
    if (button) {
      button.update({{$nbItems}});
    }
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

{{mb_include module=files template=inc_files_add_toolbar mozaic=1}}

{{foreach from=$affichageFile item=_cat key=_cat_id}}
  {{assign var=docCount value=$_cat.items|@count}}
  {{if $docCount || "dPfiles CFilesCategory show_empty"|gconf || $_cat_id == 0}}
    <div id="Category-{{$_cat_id}}" style="display: none; clear: both;" class="me-file-card-container">
      {{mb_include module=files template=inc_list_files_colonne list=$_cat.items}}
    </div>
  {{/if}}
{{/foreach}}
