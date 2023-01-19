{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title narrow"></th>
    <th class="title" style="max-width: 45%" colspan="{{$prestations_j|@count}}">Journalières</th>
    <th class="title">Ponctuelles</th>
  </tr>
  <tr>
    <th class="narrow"></th>
    {{foreach from=$prestations_j item=_prestation}}
      <th style="width: {{$width_prestation}}%" class="text">{{$_prestation->nom}}</th>
      {{foreachelse}}
      <th style="width: {{$width_prestation}}%" class="text"></th>
    {{/foreach}}

    {{mb_include module=planningOp template=inc_vw_prestations_add_ponctuelle}}
  </tr>
  {{foreach from=$dates item=_date name=foreach_date}}
    {{assign var=first_date value=$smarty.foreach.foreach_date.first}}
    {{assign var=day value=$_date|date_format:"%A"|upper|substr:0:1}}
    <tr class="{{if $_date == $relative_date}}border-bold{{/if}}">
      <td class="{{if $_date == $dnow}}current_hour{{/if}}"
          style="height: 22px;
          {{if $day == "S" || $day == "D"}}
            background-color: #ccc;
          {{elseif in_array($day, $bank_holidays)}}
            background-color: #fc0;
          {{/if}}">

        <span>
          {{if $editRights && $prestations_j|@count}}
            <button type="button" class="{{if array_key_exists($_date, $date_modified)}}edit{{else}}add{{/if}} notext" onclick="Modal.open('edit_{{$_date}}');"></button>
          {{/if}}
          <strong>{{$_date|date_format:"%d/%m"}} {{$day}}</strong>
        </span>

        {{mb_include module=planningOp template=inc_vw_prestations_date}}
      </td>

      {{foreach from=$prestations_j item=_prestation key=prestation_id name=foreach_presta}}
        {{mb_include module=planningOp template=inc_vw_prestations_case_journaliere}}
      {{foreachelse}}
        <td></td>
      {{/foreach}}

      {{mb_include module=planningOp template=inc_vw_prestations_case_ponctuelle}}
    </tr>
  {{/foreach}}

  {{if $dates_after|@count}}
    <tr>
      <th class="section" colspan="{{math equation=x+2 x=$prestations_j|@count}}">
        Prestations hors séjour
      </th>
    </tr>
    {{foreach from=$dates_after item=_date}}
      <tr>
        <td>
          <button type="button" class="cancel notext" onclick="removeLiaisons('{{$_date}}')">{{tr}}Delete{{/tr}}</button>
          <strong>{{$_date|date_format:"%d/%m"}} {{$day}}</strong>
        </td>

        {{foreach from=$prestations_j item=_prestation key=prestation_id name=foreach_presta}}
          {{mb_include module=planningOp template=inc_vw_prestations_case_journaliere}}
        {{foreachelse}}
          <td></td>
        {{/foreach}}

        {{mb_include module=planningOp template=inc_vw_prestations_case_ponctuelle}}
      </tr>
    {{/foreach}}
  {{/if}}
</table>
