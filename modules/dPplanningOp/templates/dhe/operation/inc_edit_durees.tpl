{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=show_duree_preop value=$conf.dPplanningOp.COperation.show_duree_preop}}
{{assign var=show_presence_op value=$conf.dPplanningOp.COperation.show_presence_op}}
{{assign var=show_duree_uscpo value=$conf.dPplanningOp.COperation.show_duree_uscpo}}

<table class="form">
  {{if $show_duree_preop}}
  <tr>
    <th class="halfPane">{{mb_label object=$operation field=duree_preop}}</th>
    <td>{{mb_field object=$operation field=duree_preop form=operationEdit onchange="DHE.operation.syncView(this);"}}</td>
  </tr>
  {{/if}}

  {{if $show_presence_op}}
  <tr>
    <th class="halfPane">{{mb_label object=$operation field=presence_preop}}</th>
    <td>{{mb_field object=$operation field=presence_preop form=operationEdit onchange="DHE.operation.syncView(this);"}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$operation field=presence_postop}}</th>
    <td>{{mb_field object=$operation field=presence_postop form=operationEdit onchange="DHE.operation.syncView(this);"}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$operation field=duree_bio_nettoyage}}</th>
    <td>{{mb_field object=$operation field=duree_bio_nettoyage form=operationEdit onchange="DHE.operation.syncView(this);"}}</td>
  </tr>
  {{/if}}

  {{if $show_duree_uscpo}}
  <tr>
    <th class="halfPane">{{mb_label object=$operation field=duree_uscpo}}</th>
    <td>{{mb_field object=$operation field=duree_uscpo form=operationEdit onchange="DHE.operation.syncView(this, \$V(this) + ' nuit(s)');"}} nuit(s)</td>
  </tr>
  {{/if}}
</table>