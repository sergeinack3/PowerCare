{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=edit value=false}}

<ul style="display: inline-block; padding: 0px;">
  {{foreach from=$attachments item=_attachment name=attachments}}
    <li id="{{$_attachment->_guid}}" style="list-style-type: none; margin-top: 5px; display: inline-block;{{if !$smarty.foreach.attachments.first}} margin-left: 10px;{{/if}}" class="attachment">
      <span style="cursor: pointer;" onclick="popFile('{{$_attachment->_class}}', '{{$_attachment->_id}}', 'CFile', '{{$_attachment->_ref_file->_id}}', '0');">
        <span style="margin-right: 5px;">
          <i class="msgicon fa fa-2x fa-file"></i>
        </span>

        <span style="height: 25px;">
          <span style="vertical-align: 25%;">
            <a href="#">{{$_attachment->_ref_file->file_name}} ({{$_attachment->_ref_file->_file_size}})</a>
          </span>
          {{if $edit}}
            <i class="fa fa-lg fa-times msgicon" style="cursor: pointer;" onclick="deleteAttachment('{{$_attachment->_guid}}');" title="{{tr}}CUserMessageAttachment-action-delete{{/tr}}"></i>
          {{/if}}
        </span>
      </span>
    </li>
  {{/foreach}}
</ul>