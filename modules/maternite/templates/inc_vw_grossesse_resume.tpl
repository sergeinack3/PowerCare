{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<table class="width100">
    <tr>
        <td>
            <table>
                <tr>
                    <td><strong>{{mb_title class=$object field=parturiente_id}}:</strong></td>
                    <td>{{mb_value object=$object field=parturiente_id}}</td>
                </tr>
                <tr>
                    <td><strong>{{mb_title class=$object field=terme_prevu}}:</strong></td>
                    <td>{{mb_value object=$object field=terme_prevu}}</td>
                </tr>
                <tr>
                    <td><strong>{{mb_title class=$object field=active}}:</strong></td>
                    <td>{{mb_value object=$object field=active}}</td>
                </tr>
                <tr>
                    <td><strong>{{mb_title class=$object field=nb_foetus}}:</strong></td>
                    <td>{{mb_value object=$object field=nb_foetus}}</td>
                </tr>
                <tr>
                    <td><strong>{{mb_title class=$object field=lieu_accouchement}}:</strong></td>
                    <td>{{mb_value object=$object field=lieu_accouchement}}</td>
                </tr>
            </table>
        </td>
        <td>
            <table>
                <tr>
                    <td><strong>{{mb_title class=$object field=group_id}}:</strong></td>
                    <td>{{mb_value object=$object field=group_id}}</td>
                </tr>
                <tr>
                    <td><strong>{{mb_title class=$object field=cycle}}:</strong></td>
                    <td>{{mb_value object=$object field=cycle}}</td>
                </tr>
                <tr>
                    <td><strong>{{mb_title class=$object field=multiple}}:</strong></td>
                    <td>{{mb_value object=$object field=multiple}}</td>
                </tr>
                <tr>
                    <td><strong>{{mb_title class=$object field=allaitement_maternel}}:</strong></td>
                    <td>{{mb_value object=$object field=allaitement_maternel}}</td>
                </tr>
                <tr>
                    <td><strong>{{mb_title class=$object field=rang}}:</strong></td>
                    <td>{{mb_value object=$object field=rang}}</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <table class="tbl">
                {{assign var=allaitement value=$object->_ref_last_allaitement}}
                {{if $allaitement->_id}}
                    <tr>
                        <th class="category">Allaitement</th>
                    </tr>
                    <tr>
                        <td>
                            <strong>Date de début :</strong> {{mb_value object=$allaitement field=date_debut}}
                        </td>
                    </tr>
                    {{if $allaitement->date_fin}}
                        <tr>
                            <td>
                                <strong>Date de fin :</strong> {{mb_value object=$allaitement field=date_fin}}
                            </td>
                        </tr>
                    {{/if}}
                {{/if}}
            </table>
        </td>
    </tr>

    {{if $object->_ref_grossesses_ant|@count > 0}}
        <tr>
            <th colspan="2" class="category">{{tr}}CAntecedent|pl{{/tr}}</th>
        </tr>
        <tr>
            <td colspan="2">
                <ul>
                    {{foreach from=$object->_ref_grossesses_ant item=_grossesse_ant}}
                        <li>{{mb_value object=$_grossesse_ant}}</li>
                    {{/foreach}}
                </ul>
            </td>
        </tr>
    {{/if}}

    <tr>
        <th colspan="2" class="category">{{tr}}CNaissance|pl{{/tr}}</th>
    </tr>

    {{foreach from=$object->_ref_naissances item=_naissance}}
        {{assign var=sejour value=$_naissance->_ref_sejour_enfant}}
        {{assign var=patient value=$sejour->_ref_patient}}
        <tr>
            <td colspan="2">
                {{mb_value object=$patient}}
                né(e) le
                <span onmouseover="ObjectTooltip.createEx(this, '{{$_naissance->_guid}}')">
                            {{mb_value object=$patient field=naissance}}
                        </span>

                <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}')">
                             &mdash; ( {{$sejour->_view}} )
                        </span>
            </td>
        </tr>
        {{foreachelse}}
        <tr>
            <td colspan="2">
                {{tr}}CNaissance.none{{/tr}}
            </td>
        </tr>
    {{/foreach}}
</table>
