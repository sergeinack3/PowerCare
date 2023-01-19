{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    window.print();
  });
</script>
<style>
  tr.event-data td {
    font-size: 16px;
    padding: 5px;
  }
</style>
<table class="tbl">
  <tr>
    <th class="title" colspan="5">
      <button class="print not-printable" onclick="window.print();" style="float: right;margin-left:-65px;">{{tr}}Print{{/tr}}</button>
      {{mb_include module=system template=inc_vw_mbobject object=$sejour->_ref_patient}}
      <br/>
      {{$sejour->_ref_praticien}} - {{$sejour->libelle}}
      <br/>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}')">
        {{tr}}CSejour{{/tr}} {{tr}}date.from{{/tr}} {{$sejour->entree|date_format:$conf.datetime}}
        {{tr}}date.to{{/tr}} {{$sejour->sortie|date_format:$conf.datetime}}{{*todo*}}
      </span>
    </th>
  </tr>
  <tr>
    <th>{{tr}}Date{{/tr}}</th>
    <th>{{tr}}CEvenementSSR-_heure_deb-desc{{/tr}}</th>
    <th>{{tr}}CEvenementSSR-_heure_fin-desc{{/tr}}</th>
    <th>{{tr}}CEvenementSSR-type_seance{{/tr}}</th>
    {{if !"ssr general hide_reeduc_evts_sejour"|gconf}}
      <th>{{mb_label class=CEvenementSSR field=therapeute_id}}</th>
    {{/if}}
  </tr>
  {{foreach from=$evenements item=_evenements_by_date}}
    {{assign var=count_evt value=0}}
    {{foreach from=$_evenements_by_date item=_evenements_tmp}}
      {{math assign=count_evt equation="x+y" x=$count_evt y=$_evenements_tmp|@count}}
    {{/foreach}}
    {{assign var=date_print value=0}}
    {{foreach from=$_evenements_by_date item=_evenements name="list_evt_by_date"}}
      {{foreach from=$_evenements item=_evenement name="list_evt_by_time"}}
        {{assign var=style value=""}}
        {{if $smarty.foreach.list_evt_by_date.last && $smarty.foreach.list_evt_by_time.last}}
          {{assign var=style value="border-bottom: 1px solid #aaa;"}}
        {{/if}}
        <tr class="event-data">
          {{if $smarty.foreach.list_evt_by_date.first && !$date_print}}
            {{assign var=date_print value=1}}
            <td style="vertical-align:top;border-bottom: 1px solid #aaa;" rowspan="{{$count_evt}}">
              {{$_evenement->debut|date_format:$conf.date}}
            </td>
          {{/if}}
          <td style="text-align: center;{{$style}}">{{$_evenement->_heure_deb|date_format:$conf.time}}</td>
          <td style="text-align: center;{{$style}}">{{$_evenement->_heure_fin|date_format:$conf.time}}</td>
          <td style="{{$style}}" class="text">{{$_evenement->_ref_prescription_line_element->_view}}</td>
          {{if !"ssr general hide_reeduc_evts_sejour"|gconf}}
            <td style="{{$style}}">{{$_evenement->_ref_therapeute->_view}}</td>
          {{/if}}
        </tr>
      {{/foreach}}
    {{/foreach}}
  {{/foreach}}
</table>