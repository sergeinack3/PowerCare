{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
    <tr>
        <th>
            {{mb_label object=$consult field=secteur2}}
        </th>
        <td>
            {{mb_field object=$consult field=secteur2 onchange="DHE.consult.syncView(this);"}}
        </td>
    </tr>
    <tr id="consult-edit-concerne_ALD"{{if !$patient->_id || !$patient->ald}} style="display: none;"{{/if}}>
        <th>
            {{mb_label object=$consult field=concerne_ALD}}
        </th>
        <td>
            {{mb_field object=$consult field=concerne_ALD onchange="DHE.consult.syncViewFlag(this);"}}
        </td>
    </tr>
</table>
