{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=files script=document_item ajax=true}}

{{if !$docItem || !$docItem->_guid}}
    <div class="small-warning">{{tr}}CDocumentItem.none{{/tr}}</div>
    {{mb_return}}
{{/if}}

<table class="main form">
    <tr>
        <td>
            <div class="small-info">
                {{tr}}CDA-msg-Explication{{/tr}}
                <br/>
                <br/>
                {{tr}}CDA-msg-Confirm generate CDA{{/tr}}
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <button type="button" class="me-primary tick" style="margin-left: 41%;"
                    onclick="DocumentItem.confirmGenerateCDA('{{$docItem->_guid}}')">{{tr}}Validate{{/tr}}</button>
            <button type="button" class="me-primary close"
                    onclick="Control.Modal.close()">{{tr}}Cancel{{/tr}}</button>
        </td>
    </tr>
</table>





