{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<!-- Onglet dossiers en cours volet Interventions planifiées -->

{{math equation="x+10" x='Ox\Mediboard\Pmsi\CRelancePMSI'|static:"docs"|@count assign=colspan}}

{{foreach from='Ox\Mediboard\Pmsi\CRelancePMSI'|static:"docs" item=doc}}
  {{if !"dPpmsi type_document $doc"|gconf}}
    {{math equation=x-1 x=$colspan assign=colspan}}
  {{/if}}
{{/foreach}}

{{mb_include module=system template=inc_pagination total=$countOp current=$pageOp change_page="changePageOp" step=$step}}

<table class=" main tbl">
  <tr>
    <th class="title" colspan="{{$colspan}}">
      {{tr var1=$date_min|date_format:$conf.longdate var2=$date_max|date_format:$conf.longdate}}
        PMSI-Operations list from %s to %s
      {{/tr}}
      <button class="me-float-right download notext" title="{{tr}}PMSI-export operations list title{{/tr}}"
              onclick="ExportPMSI.exportPlannedOperations()"></button>
    </th>
  </tr>
  <tr>
    <th>{{mb_title class=Coperation field=facture}}</th>
    <th class="narrow">{{mb_title class=CSejour field=_NDA}}</th>
    <th>{{mb_label class=Coperation field=chir_id}}</th>
    <th>{{mb_label class=CSejour field=patient_id}}</th>
    <th>{{mb_label class=COperation field=date}}</th>
    <th>{{mb_label class=Coperation field=time_operation}}</th>
    <th>
      {{mb_label class=Coperation  field=libelle}} +
      {{mb_label class=Coperation field=codes_ccam}}
    </th>
    {{foreach from='Ox\Mediboard\Pmsi\CRelancePMSI'|static:"docs" item=doc}}
      {{if "dPpmsi type_document $doc"|gconf}}
        <th style="width: 46px;" title="{{tr}}CRelancePMSI-{{$doc}}-desc{{/tr}}">{{tr}}CRelancePMSI-{{$doc}}-court{{/tr}}</th>
      {{/if}}
    {{/foreach}}
    <th>{{mb_title class=Coperation field=labo}}</th>
    <th>{{mb_title class=Coperation field=anapath}}</th>
    <th class="narrow">{{tr}}common-Uread{{/tr}}</th>
  </tr>
  <tbody>
    {{foreach from=$operations item=_operation}}
      {{mb_include template=current_dossiers/inc_current_interv_line}}
    {{foreachelse}}
      <tr>
        <td class="empty" colspan="{{$colspan}}">
            {{tr}}COperation.none{{/tr}}
        </td>
      </tr>
    {{/foreach}}
  </tbody>
</table>
