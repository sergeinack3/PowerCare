{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<li class="subfolder" style="margin-bottom: 5px;">
  <div class="folder{{if $folder->_guid == $selected_folder}} selected{{/if}}" data-folder="{{$folder->_guid}}" onclick="UserEmail.selectFolder('{{$account->_id}}', '{{$folder->_guid}}');" onmouseover="$('buttonfolderActions-{{$folder->_guid}}').show();" onmouseout="$('buttonfolderActions-{{$folder->_guid}}').hide();" style="font-size: 1.1em; height: 20px; padding-bottom: 2px; padding-top: 2px; padding-left: 5px; margin: 5px; cursor: pointer;">
    <span style="float:left; margin-right: 5px;">
      <i class="msgicon folder-icon fa fa-folder{{if $selected_folder == $folder->_guid}}-open{{/if}}"></i>
    </span>

    <span id="buttonfolderActions-{{$folder->_guid}}" style="display: none; position: absolute; left: 75%;">
      <i class="msgicon fa fa-lg fa-eye" title="{{tr}}CUserMailFolder-action-show_all_mails{{/tr}}" onclick="event.stopPropagation(); UserEmail.selectFolder('{{$account->_id}}', '{{$folder->_guid}}', 1);"></i>
      <i class="msgicon fa fa-lg fa-eye-slash" title="{{tr}}CUserMailFolder-action-show{{/tr}}" onclick="event.stopPropagation(); UserEmail.selectFolder('{{$account->_id}}', '{{$folder->_guid}}', 0);" style="display: none;"></i>
      <i class="msgicon fas fa-lg fa-pencil-alt" title="{{tr}}Edit{{/tr}}" onclick="event.stopPropagation(); UserEmail.editFolder('{{$account->_id}}', '{{$folder->_id}}');"></i>
    </span>

    <span class="count circled"{{if $folder->_count_mails == 0}} style="display: none;"{{/if}}>
     {{$folder->_count_mails}}
    </span>

    <span>
      {{$folder->name}}
    </span>
  </div>


  {{if $folder->_ref_children|@count != 0}}
    <ul style="list-style-type: none;{{if !$object || ($object && ($folder->_guid != $selected_folder && !array_key_exists($folder->_id, $object->_ref_ancestors)))}} display: none;{{/if}}" class="subfolders_list" id="{{$folder->_guid}}-subfolders">
      {{foreach from=$folder->_ref_children item=_subfolder}}
        {{mb_include module=messagerie template=inc_mail_folder folder=$_subfolder}}
      {{/foreach}}
    </ul>
  {{/if}}
</li>