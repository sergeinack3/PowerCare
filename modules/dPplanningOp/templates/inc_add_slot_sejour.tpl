{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=mode_operation       value=0}}
{{assign var=show_sejour_multiple value=0}}
{{assign var=form_name            value="sejourMultiple-$rank"}}
{{assign var=heure_entree_jour    value=99}}
{{assign var=heure_sortie_ambu    value=99}}
{{assign var=_duree_prevue        value=$sejour->_duree_prevue}}
{{assign var=_duree_prevue_heure  value=$sejour->_duree_prevue_heure}}

<form name="sejourMultiple-{{$rank}}" method="get">
  <table class="form">
    <tr>
      <th class="category" colspan="4">
        <button type="button" class="erase notext" style="float: right;"
                onclick="SejourMultiple.removeSlot('{{$rank}}');"></button>
        {{tr}}CSejour{{/tr}}
      </th>
    </tr>
    {{mb_include module=planningOp template=inc_entree_sortie}}
  </table>
</form>