{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr {{if !$_related_receiver->active}} class="opacity-30" {{/if}}>
    <td class="button">
        <button class="edit notext"
                onclick="FilesCategory.editRelatedReceiver('{{$_related_receiver->_id}}', '{{$files_category->_id}}', this);">
            {{tr}}Edit{{/tr}}
        </button>
    </td>
    <td class="narrow" style="text-align: center">
      {{assign var=related_receiver_id value=$_related_receiver->_id}}

      {{mb_include module="system" template="inc_form_button_active" field_name="active" object=$_related_receiver
        onComplete="FilesCategory.refreshRelatedReceiver('$related_receiver_id')"}}
    </td>
    <td>
        {{assign var=receiver value=$_related_receiver->_ref_receiver}}
        {{if $receiver->_id}}
            <span onmouseover="ObjectTooltip.createEx(this, '{{$receiver->_guid}}')">
                {{mb_value object=$receiver field=_view}}
            </span>
        {{else}}
            {{mb_value object=$_related_receiver field=type}}
        {{/if}}
    </td>
    <td class="text">
        {{mb_value object=$_related_receiver field=description}}
    </td>
</tr>
