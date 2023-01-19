{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
  <tr>
    <th class="halfPane">{{mb_label object=$operation field=materiel}}</th>
    <td>{{mb_field object=$operation field=materiel form=operationEdit onchange="DHE.operation.syncView(this);"}}</td>
  </tr>
  {{if "dPbloc CPlageOp systeme_materiel"|gconf == "expert"}}
  <tr>
    <th></th>
    <td>
      {{mb_include module=bloc template=inc_button_besoins_ressources object_id=$operation->_id type=operation_id from_dhe=1}}
    </td>
  </tr>
  {{/if}}
</table>