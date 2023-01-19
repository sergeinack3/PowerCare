{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}


{{assign var=interval value="dPbloc CPlageOp minutes_interval"|gconf}}
{{mb_ternary test=$interval value=$interval other=15 var=interval}}
{{math equation="floor(60 / x)" x=$interval assign=th_colspan}}

{{math equation="(x * y) + 1" x=$listHours|@count y=$th_colspan assign=colspan}}

<tr>
  <th colspan="{{$colspan}}" class="category">
    {{tr var1=$nbIntervByDay.$curr_day.$key_bloc}}COperation-%s intervention planned for this day|pl{{/tr}} ({{$bloc->_view}})
  </th>
</tr>
<tr>
  <th class="narrow">
    <a href="?m=bloc&tab=vw_edit_planning&date={{$curr_day}}&type_view_planning=day">
      <strong>{{$curr_day|date_format:"%a %d %b"}}</strong>
    </a>
    <br />
    {{assign var=plages_ids value=$listPlages.$curr_day.$key_bloc}}
    <form name="chg-{{$curr_day}}" action="?m={{$m}}" method="post" onsubmit="return EditPlanning.lockPlages(this);" class="not-printable">
      <input type="hidden" name="m" value="bloc" />
      <input type="hidden" name="@class" value="CPlageOp" />
      <input type="hidden" name="verrouillage" value="oui" />
      {{assign var=plageop_ids_key value=$plages_ids|@array_keys}}
      <input type="hidden" name="plageop_ids" value="{{"-"|implode:$plageop_ids_key}}" />
      {{if $bloc->_canEdit}}
        <button type="button" class="new notext me-secondary" onclick="EditPlanning.edit('','{{$curr_day}}', '{{$bloc->_id}}');">{{tr}}Create{{/tr}}</button>
        <button type="submit" class="lock notext me-tertiary">{{tr}}Lock{{/tr}}</button>
      {{/if}}
      <button type="button" class="print notext me-tertiary me-dark" onclick="EditPlanning.popPlanning('{{$curr_day}}');">{{tr}}Print{{/tr}}</button>
    </form>
  </th>
  {{foreach from=$listHours item=_hour}}
    <th colspan="{{$th_colspan}}" class="heure">{{$_hour}}:00</th>
  {{/foreach}}
</tr>
{{foreach from=$salles.$key_bloc item=_salle key=salle_id}}
{{assign var="keyHorsPlage" value="$curr_day-s$salle_id-HorsPlage"}}
<tr>
  <td class="salle" {{if $affichages.$key_bloc.$keyHorsPlage|@count}}rowspan="2"{{/if}}>
    <span onmouseover="ObjectTooltip.createEx(this, '{{$_salle->_guid}}')"
      onclick="EditPlanning.monitorDaySalle('{{$_salle->_id}}', '{{$curr_day}}');"
      {{if $_salle->color}}style="border-left: 4px solid #{{$_salle->color}}; padding-left: 4px;"{{/if}}>
      {{$_salle->nom}}
    </span>
    {{if $_salle->_blocage.$curr_day|@count}}
        <img src="images/icons/info.png" onmouseover="ObjectTooltip.createDOM(this, 'blocages_{{$salle_id}}')"/>
        <div id="blocages_{{$salle_id}}" style="display: none">
          <ul>
            {{foreach from=$_salle->_blocage.$curr_day item=_blocage}}
              <li>{{$_blocage->libelle}}</li>
            {{/foreach}}
          </ul>
        </div>
      {{/if}}
  </td>
  {{mb_include template=inc_planning_bloc_line}}
</tr>

{{if $affichages.$key_bloc.$keyHorsPlage|@count}}
<tr>
  <td colspan="{{$colspan}}" class="empty">
    <a href="?m=bloc&tab=vw_urgences&date={{$curr_day}}">
      + {{tr var1=$affichages.$key_bloc.$keyHorsPlage|@count}}CIntervHorsPlage-%s intervention ouf of range|pl{{/tr}}
    </a>
  </td>
</tr>
{{/if}}
{{foreachelse}}
<tr>
  <td colspan="{{$colspan}}" class="empty">{{tr}}CSalle.none{{/tr}}</td>
</tr>
{{/foreach}}
