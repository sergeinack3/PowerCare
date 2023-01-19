{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="filterIdentityTypes" method="post" action="?" onsubmit="return IdentityProofType.filter(this);">
    <table class="form">
        <tr>
            <th>
                <label for="labelFor_filterIdentityTypes_code" title="{{tr}}CIdentityProofType-code-desc{{/tr}}">
                    {{tr}}CIdentityProofType-code{{/tr}}
                </label>
            </th>
            <td>
                {{mb_field object=$filter field=code}}
            </td>
            <th>
                <label for="labelFor_filterIdentityTypes_label" title="{{tr}}CIdentityProofType-label-desc{{/tr}}">
                    {{tr}}CIdentityProofType-label{{/tr}}
                </label>
            </th>
            <td>
                {{mb_field object=$filter field=label}}
            </td>
        </tr>
        <tr>
            <th>
                <label for="labelFor_filterIdentityTypes_trust_level" title="{{tr}}CIdentityProofType-trust_level-desc{{/tr}}">
                    {{tr}}CIdentityProofType-code{{/tr}}
                </label>
            </th>
            <td>
                {{mb_field object=$filter field=trust_level emptyLabel="Select"}}
            </td>
            <th>
                <label for="labelFor_filterIdentityTypes_active" title="{{tr}}CIdentityProofType-active-desc{{/tr}}">
                    {{tr}}CIdentityProofType-label{{/tr}}
                </label>
            </th>
            {{me_form_bool nb_cells=1 mb_object=$filter mb_field=active}}
                {{mb_field object=$filter field=active}}
            {{/me_form_bool}}
        </tr>
        <tr>
            <td class="button" colspan="4">
                <button class="search me-primary" type="button" onclick="this.form.onsubmit();">{{tr}}Filter{{/tr}}</button>
                <button class="cancel" type="button" onclick="IdentityProofType.emptyFilters(this.form);">{{tr}}Empty{{/tr}}</button>
                <button class="add" type="button" onclick="IdentityProofType.edit();" title>{{tr}}Add{{/tr}}</button>
            </td>
        </tr>
    </table>
</form>
