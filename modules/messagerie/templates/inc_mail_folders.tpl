{{*
 * @package Mediboard\messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul class="list-folders" style="list-style-type: none; position: relative; margin-top: 10px; margin-right: 10px;">
  {{foreach from=$folders key=_name item=_data}}
    <li style="margin-bottom: 5px;">
      <div class="folder{{if $_name == $selected_folder}} selected{{/if}}" data-folder="{{$_name}}" onclick="UserEmail.selectFolder('{{$account->_id}}', '{{$_name}}');" onmouseover="$('buttonfolderActions-{{$_name}}').show();" onmouseout="$('buttonfolderActions-{{$_name}}').hide();" style="font-size: 1.1em; height: 20px; padding-bottom: 2px; padding-top: 2px; padding-left: 5px; margin: 5px; cursor: pointer;">
        <span style="float:left; margin-right: 5px;">
          <i class="msgicon folder-icon fa fa-folder{{if $selected_folder == $_name}}-open{{/if}}"></i>
        </span>

      <span id="buttonfolderActions-{{$_name}}" style="display: none; position: absolute; left: 75%;">
        <i class="msgicon fa fa-lg fa-eye" title="{{tr}}CUserMailFolder-action-show_all_mails{{/tr}}" onclick="event.stopPropagation(); UserEmail.selectFolder('{{$account->_id}}', '{{$_name}}', 1);"></i>
        <i class="msgicon fa fa-lg fa-eye-slash" title="{{tr}}CUserMailFolder-action-show_all_mails{{/tr}}" onclick="event.stopPropagation(); UserEmail.selectFolder('{{$account->_id}}', '{{$_name}}', 0);" style="display: none;"></i>
      </span>

        <span class="count circled"{{if $_data.count == 0}} style="display: none;"{{/if}}>
         {{$_data.count}}
        </span>

        <span>
          {{tr}}CUserMail-title-{{$_name}}{{/tr}}
        </span>
      </div>

      {{if $_data.folders|@count != 0}}
        <ul style="list-style-type: none;{{if ($_name != $selected_folder && !$object) || ($object && $_name != $object->type)}} display: none;{{/if}}" class="subfolders_list" id="{{$_name}}-subfolders">
          {{foreach from=$_data.folders item=_subfolder}}
            {{mb_include module=messagerie template=inc_mail_folder folder=$_subfolder}}
          {{/foreach}}
        </ul>
      {{/if}}
    </li>
  {{/foreach}}
</ul>