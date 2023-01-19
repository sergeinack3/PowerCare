{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
  <tr>
    <th class="halfPane">
      {{mb_label object=$operation field=depassement}}
    </th>
    <td>
      {{mb_field object=$operation field=depassement onchange="DHE.operation.syncView(this, 'DH ' + \$V(this) + DHE.configs.currency);"}}
    </td>
  </tr>
  <tr>
    <th>
      {{mb_label object=$operation field=forfait}}
    </th>
    <td>
      {{mb_field object=$operation field=forfait onchange="DHE.operation.syncView(this, 'Forf. clin. ' + \$V(this) + DHE.configs.currency);"}}
    </td>
  </tr>
  <tr>
    <th>
      {{mb_label object=$operation field=fournitures}}
    </th>
    <td>
      {{mb_field object=$operation field=fournitures onchange="DHE.operation.syncView(this, 'Fournitures ' + \$V(this) + DHE.configs.currency);"}}
    </td>
  </tr>
  <tr>
    <th>
      {{mb_label object=$operation field=conventionne}}
    </th>
    <td>
      {{mb_field object=$operation field=conventionne onchange="DHE.operation.syncViewFlag(this);"}}
    </td>
  </tr>
</table>