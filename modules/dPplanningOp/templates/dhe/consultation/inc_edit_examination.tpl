{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
  <tr>
    <th>
      {{mb_label object=$consult field=motif}}
    </th>
    <td>
      {{mb_field object=$consult field=motif onchange="DHE.consult.syncView(this);" class=autocomplete form=consultationEdit}}
    </td>
  </tr>
  <tr>
    <th>
      {{mb_label object=$consult field=rques}}
    </th>
    <td>
      {{mb_field object=$consult field=rques onchange="DHE.consult.syncView(this);" class=autocomplete form=consultationEdit}}
    </td>
  </tr>
  <tr>
    <th>
      {{mb_label object=$consult field=traitement}}
    </th>
    <td>
      {{mb_field object=$consult field=traitement onchange="DHE.consult.syncView(this);" class=autocomplete form=consultationEdit}}
    </td>
  </tr>
  <tr>
    <th>
      {{mb_label object=$consult field=histoire_maladie}}
    </th>
    <td>
      {{mb_field object=$consult field=histoire_maladie onchange="DHE.consult.syncView(this);" class=autocomplete form=consultationEdit}}
    </td>
  </tr>
</table>