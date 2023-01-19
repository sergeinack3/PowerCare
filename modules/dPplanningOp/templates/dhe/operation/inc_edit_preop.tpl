{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
  <tr>
    <th class="halfPane">{{mb_label object=$operation field=examen}}</th>
    <td>{{mb_field object=$operation field=examen form="operationEdit" onchange="DHE.operation.syncView(this, 'Bilan: ' + \$V(this));"}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$operation field=exam_per_op}}</th>
    <td>{{mb_field object=$operation field=exam_per_op form="operationEdit" onchange="DHE.operation.syncView(this, 'Examen: ' + \$V(this));"}}</td>
  </tr>
</table>