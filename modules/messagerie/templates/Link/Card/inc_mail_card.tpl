{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{* default *}}
{{mb_default var=categories value=[]}}

<div class="MessagingLinkCard">
    <div class="MessagingLinkCard-view">
        <div class="MessagingLinkCard-preaction">
            <input type="checkbox"
                   name="checkboxItem"
                   data-id="{{$mail->object_class}}-{{$mail->object_id}}"
                   onchange="MessagingLink.selectAttachment(this)">
        </div>
        <div class="MessagingLinkCard-content"
             onclick="MessagingLink.selectAttachment(this)">
            <div class="MessagingLinkCard-icon">
                <i class="MessagingIcon mdi mdi-text-box mdi-48px"></i>
            </div>
            <div class="MessagingLinkCard-title">
                <span class="MessagingLinkCard-titleName">
                    {{$mail->name}}
                </span>
                <span class="MessagingLinkCard-titleExtension">
                    {{$mail->file_extension}}
                </span>
            </div>
        </div>
        <div class="MessagingLinkCard-action">
            <button type="button" class="me-tertiary no-text"
                    title="{{tr}}CMessagingLink-Title-Edit item{{/tr}}"
                    data-edit_mode="false"
                    onclick="MessagingLink.editAttachment(this)">
                <i class="mdi mdi-pencil mdi-18px"></i>
            </button>
        </div>
    </div>
    <div class="MessagingLinkCard-edit">
        <form name="edit-{{$mail->object_class}}-{{$mail->object_id}}" method="post" onsubmit="return false">
            <div class="MessagingLinkCard-editForm">
                <div class="MessagingLinkCard-editField">
                    {{me_form_field mb_object=$mail mb_field="name"}}
                        {{mb_field object=$mail field="name" onkeyup="MessagingLink.updateAttachmentName(this)" onchange="File.checkFileName(this.value)"}}
                    {{/me_form_field}}
                    {{me_form_field mb_object=$mail mb_field="category_id"}}
                        <select name="category_id" class="{{$mail->_props.category_id}}">
                            <option value="">-- {{tr}}Choose{{/tr}}</option>
                            {{foreach from=$categories item=category}}
                                <option value="{{$category->_id}}">{{$category->_view}}</option>
                            {{/foreach}}
                        </select>
                    {{/me_form_field}}
                    {{mb_field object=$mail field="file_extension" hidden=true}}
                </div>
            </div>
        </form>
    </div>
</div>

