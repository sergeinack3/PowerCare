{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=mozaic value=0}}
{{unique_id var=unique_id}}

<script>
  Main.add(function() {
    Control.Tabs.create("tabs-files-{{$object->_class}}-{{$unique_id}}", true);
  })
</script>

{{if $show_actions}}
  {{if $can_files->edit && $object->_can->read}}
    <button class="new me-tertiary" onclick="File.upload('{{$object->_class}}','{{$object->_id}}', '{{$category_id}}')" >
      {{tr}}CFile-title-create{{/tr}}
    </button>

    {{if $mozaic}}
      <button class="new me-tertiary me-dark" onclick="File.createMozaic('{{$object->_class}}-{{$object->_id}}', '')">
        {{tr}}CFile-create-mozaic{{/tr}}
      </button>
    {{/if}}

    {{if "drawing"|module_active}}
      <button class="drawing notext me-tertiary me-dark" type="button" onclick="editDrawing(null, null, '{{$object->_guid}}');">
        {{tr}}CDrawingItem.new{{/tr}}
      </button>
    {{/if}}

    {{if "mbHost"|module_active && $app->user_prefs.upload_mbhost}}
      <span class="me-widget-file-mbHost-button">
        {{mb_include module=mbHost template=inc_button_upload_file}}
      </span>
    {{/if}}
  {{/if}}
{{/if}}

{{if $object->_nb_cancelled_files}}
  <button class="hslip me-tertiary me-dark" style="float: right;" data-show=""
          onclick="File.showCancelled(this, $('list_{{$object->_class}}{{$object->_id}}'))">
    Afficher / Masquer {{$object->_nb_cancelled_files}} fichier(s) annulé(s)
  </button>
{{/if}}

{{if $can->admin}}
  <form name="DeleteAll-{{$object->_guid}}" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
    <input type="hidden" name="m" value="dPfiles" />
    <input type="hidden" name="dosql" value="do_file_multi_delete" />
    <input type="hidden" name="del" value="1" />
    <input type="hidden" name="object_guid" value="{{$object->_guid}}">

    <button class="trash me-tertiary me-dark" type="button" style="float: right;" onclick="File.removeAll(this, '{{$object->_guid}}')">
        {{tr}}Delete-all{{/tr}}
    </button>
  </form>
{{/if}}

<div id="list_{{$object->_class}}{{$object->_id}}" class="text">
  {{if $affichageFile}}
    <ul id="tabs-files-{{$object->_class}}-{{$unique_id}}" class="control_tabs small">
      {{foreach from=$affichageFile item=_cat key=_cat_id}}
        {{assign var=docCount value=$_cat.items|@count}}
        <li>
          <a href="#Category-files-{{$object->_class}}-{{$_cat_id}}-{{$unique_id}}">
            {{$_cat.name}}
            <small>({{$docCount}})</small>
          </a>
        </li>
      {{/foreach}}
    </ul>
  {{/if}}

  {{mb_include module=files template=inc_widget_list_files unique_id=$unique_id}}
</div>
