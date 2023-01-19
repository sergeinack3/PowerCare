{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{math equation=100/x x=$dates|@count assign=width_date}}

<table class="tbl">
  <tr>
    <th class="title" colspan="{{math equation=x+2 x=$dates|@count}}">
      Autres prestations
    </th>
  </tr>
  <tr>
    <th class="title narrow"></th>
    <th class="title narrow"></th>
    {{foreach from=$dates item=_affectation_id key=_date name=foreach_date}}
      {{assign var=first_date value=$smarty.foreach.foreach_date.first}}
      {{assign var=day value=$_date|date_format:"%A"|upper|substr:0:1}}
      <td class="{{if $_date == $dnow}}current_hour{{/if}}"
        style="width: {{$width_date}}%; height: 22px;
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
    {{/foreach}}
  </tr>
  <tr>
    <th class="title" rowspan="{{$prestations_j|@count}}">Journalières</th>

    {{foreach from=$prestations_j item=_prestation key=prestation_id name=foreach_presta}}
      <th style="height: 22px;">
        {{$_prestation}}
      </th>
      {{foreach from=$dates item=_date name=foreach_date}}
        {{assign var=first_date value=$smarty.foreach.foreach_date.first}}
        {{assign var=day value=$_date|date_format:"%A"|upper|substr:0:1}}

        {{mb_include module=planningOp template=inc_vw_prestations_case_journaliere}}
      {{/foreach}}
      </tr>
      <tr>
    {{/foreach}}
  </tr>

  <tr>
    <th class="title">Ponctuelles</th>

    {{mb_include module=planningOp template=inc_vw_prestations_add_ponctuelle}}

    {{foreach from=$dates item=_date name=foreach_date}}
      {{assign var=first_date value=$smarty.foreach.foreach_date.first}}
      {{assign var=day value=$_date|date_format:"%A"|upper|substr:0:1}}

      {{mb_include module=planningOp template=inc_vw_prestations_case_ponctuelle}}
    {{/foreach}}
  </tr>
</table>
