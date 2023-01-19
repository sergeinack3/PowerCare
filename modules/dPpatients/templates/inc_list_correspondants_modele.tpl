{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=inc_pagination current=$start_corres step=$step_corres total=$nb_correspondants change_page=refreshPageCorrespondant}}

<table class="tbl">
    <tr>
        <th class="category narrow"></th>
        {{if $is_admin}}
            <th>{{mb_title class=CCorrespondantPatient field=function_id}}</th>
        {{/if}}
        <th>{{mb_title class=CCorrespondantPatient field=nom}}</th>
        <th>{{mb_title class=CCorrespondantPatient field=prenom}}</th>
        <th>{{mb_title class=CCorrespondantPatient field=surnom}}</th>
        <th>{{mb_title class=CCorrespondantPatient field=naissance}}</th>
        <th>{{mb_title class=CCorrespondantPatient field=adresse}}</th>
        <th>{{mb_title class=CCorrespondantPatient field=cp}}/{{mb_title class=CCorrespondantPatient field=ville}}</th>
        <th>{{mb_title class=CCorrespondantPatient field=tel}}</th>
        <th>{{mb_title class=CCorrespondantPatient field=mob}}</th>
        <th>{{mb_title class=CCorrespondantPatient field=fax}}</th>
        <th>{{mb_title class=CCorrespondantPatient field=relation}}</th>
        {{if $conf.ref_pays == 1}}
            <th>{{mb_title class=CCorrespondantPatient field=urssaf}}</th>
        {{/if}}
        <th>{{mb_title class=CCorrespondantPatient field=email}}</th>
        <th>{{mb_title class=CCorrespondantPatient field=remarques}}</th>
    </tr>

    {{foreach from=$correspondants item=_correspondant}}
        <tr>
            <td>
                <button type="button" class="edit notext me-tertiary"
                        onclick="Correspondant.edit('{{$_correspondant->_id}}', null, refreshPageCorrespondant)">
                </button>
            </td>

            {{if $is_admin}}
                <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_correspondant->_ref_function->_guid}}')">
            {{mb_value object=$_correspondant field=function_id}}
          </span>
                </td>
            {{/if}}
            <td>
                {{mb_value object=$_correspondant field=nom}}
            </td>
            <td>
                {{mb_value object=$_correspondant field=prenom}}
            </td>
            <td>{{mb_value object=$_correspondant field=surnom}}</td>
            <td>
                {{mb_value object=$_correspondant field=naissance}}
            </td>
            <td class="compact">
                {{mb_value object=$_correspondant field=adresse}}
            </td>
            <td class="compact">
                {{mb_value object=$_correspondant field=cp}}
                {{mb_value object=$_correspondant field=ville}}
            </td>
            <td>
                {{mb_value object=$_correspondant field=tel}}
            </td>
            <td>
                {{mb_value object=$_correspondant field=mob}}
            </td>
            <td>
                {{mb_value object=$_correspondant field=fax}}
            </td>
            <td>
                {{mb_value object=$_correspondant field=relation}}
                {{if $_correspondant->relation != "employeur"}}
                    {{if $_correspondant->parente == "autre"}}
                        &mdash; {{mb_value object=$_correspondant field=parente_autre}}
                    {{elseif $_correspondant->parente}}
                        &mdash; {{mb_value object=$_correspondant field=parente}}
                    {{/if}}
                {{/if}}
            </td>
            {{if $conf.ref_pays == 1}}
                <td>
                    {{if $_correspondant->relation == "employeur"}}
                        {{mb_value object=$_correspondant field=urssaf}}
                    {{/if}}
                </td>
            {{/if}}
            <td>{{mb_value object=$_correspondant field=email}}</td>
            <td>
                {{mb_value object=$_correspondant field=remarques}}
            </td>
        </tr>
        {{foreachelse}}
        <tr>
            <td colspan="20">{{tr}}CCorrespondantPatient.none{{/tr}}</td>
        </tr>
    {{/foreach}}
</table>
