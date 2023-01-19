{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl" data-activePage="{{$page}}">
    <tr>
        <td colspan="6">
            {{mb_include module=system template=inc_pagination total=$total step=25 current=$page change_page=IdentityProofType.changePage}}
        </td>
    </tr>
    <thead>
        <tr>
            <th>{{mb_title class=CIdentityProofType field=label}}</th>
            <th>{{mb_title class=CIdentityProofType field=code}}</th>
            <th>{{mb_title class=CIdentityProofType field=trust_level}}</th>
            <th>{{mb_title class=CIdentityProofType field=active}}</th>
            <th>{{mb_title class=CIdentityProofType field=validate_identity}}</th>
            <th class="narrow"></th>
        </tr>
    </thead>
    <tbody>
        {{foreach from=$types item=type}}
            <tr>
                <td>
                    {{mb_value object=$type field=label}}
                </td>
                <td>
                    {{mb_value object=$type field=code}}
                </td>
                <td>
                    {{mb_value object=$type field=trust_level}}
                </td>
                <td>
                    {{mb_value object=$type field=active}}
                </td>
                <td>
                    {{if $type->trust_level === '3'}}
                      {{mb_value object=$type field=validate_identity}}
                    {{/if}}
                </td>
                <td class="narrow">
                    <button class="edit notext me-primary" type="button" onclick="IdentityProofType.edit('{{$type->_id}}');">{{tr}}Edit{{/tr}}</button>
                    {{if $type->isEditable()}}
                        <form name="delete{{$type->_guid}}" method="post" action="?" onsubmit="return IdentityProofType.delete(this);">
                            {{mb_class object=$type}}
                            {{mb_key object=$type}}
                            <input type="hidden" name="del" value="1">
                            <button class="trash notext" onclick="this.form.onsubmit();">{{tr}}Delete{{/tr}}</button>
                        </form>
                    {{/if}}
                </td>
            </tr>
        {{foreachelse}}
            <tr>
                <td class="empty" colspan="6">
                    {{tr}}CIdentityProofType.none{{/tr}}
                </td>
            </tr>
        {{/foreach}}
    </tbody>
</table>
