{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
    {{if 'maternite'|module_active && @$modules.maternite->_can->read}}
        <tr>
            <th>
                {{mb_label object=$sejour field=grossesse_id}}
            </th>
            <td>
                {{mb_include module=maternite template=inc_input_grossesse object=$sejour patient=$patient}}
            </td>
        </tr>
    {{/if}}
    <tr>
        <th>
            {{mb_label object=$sejour field=ATNC}}
        </th>
        <td>
            {{mb_field object=$sejour field=ATNC onchange="DHE.sejour.syncViewFlag(this);"}}
        </td>
    </tr>
    <tr id="sejour-edit-uhcd"{{if $sejour->type != 'comp'}} style="display: none;"{{/if}}>
        <th>
            {{mb_label object=$sejour field=UHCD}}
        </th>
        <td>
            {{mb_field object=$sejour field=UHCD onchange="DHE.sejour.syncViewFlag(this);"}}
        </td>
    </tr>
    <tr id="sejour-edit-reanimation"{{if $sejour->type != 'comp'}} style="display: none;"{{/if}}>
        <th>
            {{mb_label object=$sejour field=reanimation}}
        </th>
        <td>
            {{mb_field object=$sejour field=reanimation onchange="DHE.sejour.syncViewFlag(this); DHE.sejour.changeReanimation();"}}
        </td>
    </tr>
    <tr>
        {{mb_include module=planningOp template=inc_field_handicap onchange="DHE.sejour.syncViewFlag(this, \$T('CSejour.handicap.' + \$V(this)));"}}
    </tr>
    <tr>
        <th>
            {{mb_label object=$sejour field=consult_accomp}}
        </th>
        <td>
            {{mb_field object=$sejour field=consult_accomp onchange="DHE.sejour.syncViewFlag(this, null, ['oui']);"}}
        </td>
    </tr>

    <tr>
        <th>
            {{mb_label object=$sejour field=date_accident}}
        </th>
        <td>
            {{mb_field object=$sejour field=date_accident register=true form=sejourEdit onchange="DHE.sejour.syncViewFlag(this, 'Date: ' + \$V(this.form.date_accident_da)); DHE.sejour.changeDateAccident();"}}
        </td>
    </tr>
    <tr id="sejour-edit-nature_accident"{{if !$sejour->date_accident}} style="display: none;"{{/if}}>
        <th>
            {{mb_label object=$sejour field=nature_accident}}
        </th>
        <td>
            {{mb_field object=$sejour field=nature_accident onchange="DHE.sejour.syncViewFlag(this.form.date_accident, 'Date: ' + \$V(this.form.date_accident_da) + ', Nature: ' + \$T('CSejour.nature_accident.' + \$V(this)));" emptyLabel='Choose'}}
        </td>
    </tr>

    <tr>
        <th>
            {{mb_label object=$sejour field=isolement}}
        </th>
        <td>
            {{mb_field object=$sejour field=isolement onchange="DHE.sejour.syncViewFlag(this, 'Du ' + \$V(this.form._isolement_date_da) + ' au ' + \$V(this.form.isolement_fin_da)); DHE.sejour.changeIsolement();"}}
        </td>
    </tr>
    <tbody id="sejour-edit-isolement"{{if !$sejour->isolement}} style="display: none;"{{/if}}>
    <tr>
        <th>
            {{mb_label object=$sejour field=_isolement_date}}
        </th>
        <td>
            {{mb_field object=$sejour field=_isolement_date form=sejourEdit register=true onchange="DHE.sejour.syncViewFlag(this.form.down('input[name=\"isolement\"]:checked'), 'Du ' + \$V(this.form._isolement_date_da) + ' au ' + \$V(this.form.isolement_fin_da));"}}
        </td>
    </tr>
    <tr>
        <th>
            {{mb_label object=$sejour field=isolement_fin}}
        </th>
        <td>
            {{mb_field object=$sejour field=isolement_fin form=sejourEdit register=true onchange="DHE.sejour.syncViewFlag(this.form.down('input[name=\"isolement\"]:checked'), 'Du ' + \$V(this.form._isolement_date_da) + ' au ' + \$V(this.form.isolement_fin_da));"}}
        </td>
    </tr>
    <tr>
        <th>
            {{mb_label object=$sejour field=raison_medicale}}
        </th>
        <td>
            {{mb_field object=$sejour field=raison_medicale}}
        </td>
    </tr>
    </tbody>
    <tr>
        <th>
            {{mb_label object=$sejour field=rques}}
        </th>
        <td>
            {{mb_field object=$sejour field=rques onchange="DHE.sejour.syncView(this);"}}
        </td>
    </tr>
    <tr>
        <th>
            {{mb_label object=$sejour field=convalescence}}
        </th>
        <td>
            {{mb_field object=$sejour field=convalescence onchange="DHE.sejour.syncView(this);"}}
        </td>
    </tr>
</table>
