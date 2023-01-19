{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$docItem || !$docItem->_guid}}
    <div class="small-warning">{{tr}}CDocumentItem.none{{/tr}}</div>
    {{mb_return}}
{{/if}}

<form name="addTypeDocument" method="post" action="?"
      onsubmit="return onSubmitFormAjax(this, function() {Control.Modal.close(); DocumentItem.generateCDA('{{$docItem->_guid}}') });">
    {{mb_key object=$docItem}}
    {{mb_class object=$docItem}}

    <table class="main form">
        <tr>
            <th>{{mb_label object=$docItem field=type_doc_dmp}}</th>
            <td>{{mb_field object=$docItem field=type_doc_dmp emptyLabel="Choose"}}</td>
        </tr>
        <tr>
            <td colspan="2">
                <button type="button" class="me-primary tick" style="margin-left: 45%;"
                        onclick="this.form.onsubmit();">{{tr}}Save{{/tr}}</button>
            </td>
        </tr>
    </table>
</form>




