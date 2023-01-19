{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(window.print);
</script>

{{foreach from=$dates_planning item=_date_planning name=planning}}
  {{assign var=result value=$results.$_date_planning}}
  {{assign var=_dates value=$dates.$_date_planning}}
  {{math equation=x*2+1 x=$_dates|@count assign=colspan}}
    
  <h1 style="page-break-before: avoid; text-align: center">
    Planning opératoire du {{$result.date_min|date_format:$conf.date}} au {{$result.date_max|date_format:$conf.date}}
  </h1>
  <table class="tbl" style="table-layout: fixed; text-align: center; {{if !$smarty.foreach.planning.last}}page-break-after: always{{/if}}">
    <thead>
      <tr>
        <th style="width: 8%;">
          <button type="button" class="print not-printable notext" onclick="window.print()"></button>
        </th>
        {{foreach from=$_dates item=_date}}
          <th class="text">{{$_date|date_format:"%A"|ucfirst}} matin</th>
          <th class="text">{{$_date|date_format:"%A"|ucfirst}} après-midi</th>
        {{/foreach}}
      </tr>
    </thead>
    {{foreach from=$blocs item=_bloc key=_bloc_id}}
      <tr>
        <th class="section" colspan="{{$colspan}}">
          {{$_bloc}}
        </th>
      </tr>
      {{foreach from=$_bloc->_ref_salles item=_salle key=_salle_id}}
        <tr>
          <th style="height: 30px;" class="narrow text">
            {{$_salle->nom}}
          </th>
          {{foreach from=$result.$_salle_id item=_result_by_date}}
            {{foreach from=$_result_by_date item=_result_by_creneau}}
              <td class="text compact">
                {{$_result_by_creneau}}
              </td>
            {{/foreach}}
          {{/foreach}}
        </tr>
      {{/foreach}}
    {{/foreach}}
  </table>
{{/foreach}}