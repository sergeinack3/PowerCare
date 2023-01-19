{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=jfse script=Jfse ajax=$ajax}}
{{mb_script module=jfse script=Cps ajax=$ajax}}

<button type="button" style="min-height: 34px; min-width: 32px; padding: 2px;" onclick="Cps.read();">
    <span class="fa fa-stack fa-lg" style="color: green;">
        <i class="fa fa-square fa-stack-2x"></i>
        <i class="fas fa-credit-card fa-stack-1x fa-inverse"></i>
    </span>
</button>

<span onmouseover="ObjectTooltip.createDOM(this, 'cps_infos');">
    {{$jfse_user->last_name}} {{$jfse_user->first_name}}
    <div class="empty me-inline">{{mb_value object=$jfse_user->situation field=speciality_label}} {{mb_value object=$jfse_user->situation field=invoicing_number}}</div>
</span>

{{if $jfse_user->substitution_session}}
    <div class="small-info">
        {{tr var1=$jfse_user->substitute_first_name var2=$jfse_user->substitute_last_name var3=$jfse_user->substitute_number}}
            CJfseUserView-msg-substitution_session
        {{/tr}}
    </div>
{{/if}}

<div id="cps_infos" style="display: none;">
    <table>
        <tr>
            <th>{{tr}}CJfseUserView-national_identification_number{{/tr}}</th>
            <td class="rpps">
                {{mb_value object=$jfse_user field=national_identification_number}}
            </td>
        </tr>
        <tr>
            <th>{{tr}}CJfseUserView-Speciality{{/tr}}</th>
            <td class="speciality">
                {{mb_value object=$jfse_user->situation field=speciality_label}}
            </td>
        </tr>
        <tr>
            <th>{{tr}}CJfseUserView-Contracted{{/tr}}</th>
            <td class="contracted">
                {{mb_value object=$jfse_user->situation field=convention_label}}
            </td>
        </tr>
        <tr>
            <th>{{tr}}CJfseUserView-invoicing_number{{/tr}}</th>
            <td class="invoicing_number">
                {{mb_value object=$jfse_user->situation field=invoicing_number}}
            </td>
        </tr>
    </table>
</div>
