{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="select_attach" method="get" action="?">
  <table class="tbl" id="list_attach">
    <tr>
      <th>Contenus</th>
      <th><input type="checkbox" onclick="UserEmail.toggleSelect('list_attach', this.checked, 'checkbox_att'); checkrelation()" checked="checked" value="0"/></th>
    </tr>

  {{if $mail->_text_plain->_id}}

    <tr>
      <td>
        {{if $mail->_text_html->_id}}
          {{assign var=checkbox_name value='attach_html'}}
          {{assign var=checkbox_value value=$mail->_text_html->_id}}
          {{assign var=class value='html'}}
          <iframe src="?m={{$m}}&raw=vw_html_content&mail_id={{$mail->_id}}" style="width:100%;"></iframe>
        {{else}}
          {{assign var=checkbox_name value='attach_plain'}}
          {{assign var=checkbox_value value=$mail->_text_plain->_id}}
          {{assign var=class value='plain'}}
          <textarea style="width:100%; height:100px;">
            {{$mail->_text_plain->content}}
          </textarea>
        {{/if}}
      </td>
      <td class="{{$class}}">
        <input type="checkbox" name="{{$checkbox_name}}" value="{{$checkbox_value}}" onclick="checkrelation();"{{if !$mail->_ref_linked_files|@count}} checked{{/if}}>
      </td>
    </tr>
    {{if $mail->_ref_linked_files|@count}}
      <tr>
        <td colspan="2">
          Liens:
          <ul>
            {{foreach from=$mail->_ref_linked_files item=_link}}
              <li>
                <span onmouseover="ObjectTooltip.createEx(this, '{{$_link->_ref_file->_ref_object->_guid}}')">{{$_link->_ref_file->_ref_object}}</span>
                <button type="button" onclick="UserEmail.deleteLinkAttachment('{{$mail->_id}}', '{{$_link->_id}}');" title="{{tr}}Delete{{/tr}}">
                  <i class="msgicon fa fa-unlink"></i>
                </button>
              </li>
            {{/foreach}}
          </ul>
        </td>
      </tr>
    {{/if}}
    <tr>
      <td colspan="2">
        <label>
          Renommer : <input type="text" name="rename_text" value="{{$mail->subject}}.txt" onchange="checkrelation()" />
        </label>
        <label>
          Catégorie : <select name="category_id" style="width:12em;" onchange="checkrelation()">
            <option value="">&mdash; Sans catégorie</option>
            {{foreach from=$cats item=_cat}}
              <option value="{{$_cat->_id}}">{{$_cat}}</option>
            {{/foreach}}
          </select>
        </label>
      </td>
    </tr>
  {{/if}}

  {{assign var=attachments value=$mail->_attachments}}
  {{foreach from=$attachments item=_attachment}}
    <tr class="attachment">
      <td style="text-align: center;">
        {{if $_attachment->_file && $_attachment->_file->_id}}
          <a onclick="popFile('{{$_attachment->_file->object_class}}', '{{$_attachment->_file->object_id}}', 'CFile', '{{$_attachment->_file->_id}}', '0');"  href="#" title="{{tr}}CMailAttachments-openAttachment{{/tr}}">
            {{thumbnail document=$_attachment->_file profile=small alt="Preview" style="max-width:50px"}}<br/>
            {{$_attachment->_file->file_name}}<br/>
          </a>
        {{else}}
          <img src="images/pictures/unknown.png" alt=""/><br/>
          {{$_attachment->name}}
        {{/if}}
      </td>
      <td class="check">
        <input type="checkbox" class="check_att" name="checkbox_att" {{if !$_attachment->_ref_linked_files|@count}} checked{{/if}} value="{{$_attachment->_id}}" onclick="checkrelation();">
      </td>
    </tr>
    {{if $_attachment->_ref_linked_files|@count}}
      <tr>
        <td colspan="2">
          Liens:
          <ul>
            {{foreach from=$_attachment->_ref_linked_files item=_link}}
              <li>
                <span onmouseover="ObjectTooltip.createEx(this, '{{$_link->_ref_file->_ref_object->_guid}}')">{{$_link->_ref_file->_ref_object}}</span>
                <button type="button" onclick="UserEmail.deleteLinkAttachment('{{$mail->_id}}', '{{$_link->_id}}');" title="{{tr}}Delete{{/tr}}">
                  <i class="msgicon fa fa-unlink"></i>
                </button>
              </li>
            {{/foreach}}
          </ul>
        </td>
      </tr>
    {{/if}}
  {{foreachelse}}
    <tr><td colspan="3" class="empty">{{tr}}CMailAttachments-none{{/tr}}</td></tr>
  {{/foreach}}
  </table>
</form>
