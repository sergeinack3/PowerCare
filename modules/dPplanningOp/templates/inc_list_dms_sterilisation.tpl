{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="halfPane">
      {{mb_title class=Ox\Mediboard\PlanningOp\CMaterielOperatoire field=dm_id}}
    </th>
    <th>
      {{mb_title class=Ox\Mediboard\PlanningOp\CMaterielOperatoire field=qte_prevue}}
    </th>
  </tr>

  {{foreach from=$dms item=_dm}}
  <tr>
    <td>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$_dm.dm->_guid}}');">
        {{$_dm.dm->_view}}
      </span>
    </td>
    <td>
      {{$_dm.quantite}}
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td class="empty" colspan="2">{{tr}}CDM.none{{/tr}}</td>
  </tr>
  {{/foreach}}
</table>