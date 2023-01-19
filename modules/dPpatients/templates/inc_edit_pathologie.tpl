{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=pathologie_guid value=$pathologie->_guid}}
<form name="editPathologie_{{$pathologie_guid}}" method="post"
      onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
    <input type="hidden" name="m" value="patients"/>
    <input type="hidden" name="dosql" value="do_pathologie_aed"/>
    {{mb_key object=$pathologie}}
    <input type="hidden" name="del" value="0"/>

    <table class="form">
        <tr>
            <th>
                {{mb_label object=$pathologie field=debut}}
            </th>
            <td style="height: 20px;">
                {{mb_field object=$pathologie field=debut form="editPathologie_$pathologie_guid" register=true}}
            </td>
            <td style="width: 70%" rowspan="3">
                {{mb_field object=$pathologie field=pathologie form="editPathologie_$pathologie_guid"}}
            </td>
        </tr>
        <tr>
            <th>
                {{mb_label object=$pathologie field=fin}}
            </th>
            <td>
                {{mb_field object=$pathologie field=fin form="editPathologie_$pathologie_guid" register=true}}
            </td>
        </tr>
        {{if $pathologie->type !== "probleme"}}
            <tr>
                <th>
                    {{mb_label object=$pathologie field=ald}}
                </th>
                <td>
                    {{mb_field object=$pathologie field=ald typeEnum=checkbox}}
                </td>
            </tr>
        {{/if}}
        <tr>
            <td colspan="3" class="button">
                <button type="button" class="save" onclick="this.form.onsubmit()">{{tr}}Save{{/tr}}</button>
            </td>
        </tr>
    </table>
</form>
