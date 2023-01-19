{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
  <tr>
    <th class="halfPane">{{mb_label object=$operation field=exam_extempo}}</th>
    <td>{{mb_field object=$operation field=exam_extempo onchange="DHE.operation.syncViewFlag(this);"}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$operation field=rques}}</th>
    <td>{{mb_field object=$operation field=rques form="operationEdit" onchange="DHE.operation.syncView(this, 'Rem: ' + \$V(this));"}}</td>
  </tr>
  <tr>
    <th class="halfPane">{{mb_label object=$operation field=info}}</th>
    <td>{{mb_field object=$operation field=info onchange="DHE.operation.syncViewFlag(this);"}}</td>
  </tr>
</table>