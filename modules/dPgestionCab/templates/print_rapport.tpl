{{*
 * @package Mediboard\GestionCab
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr class="clear">
    <th colspan="100">
      <strong>
      <a href="#" onclick="window.print()">
        Rapport comptable du {{$filter->_date_min|date_format:$conf.date}}
        {{if $filter->_date_min != $filter->_date_max}}
        au {{$filter->_date_max|date_format:$conf.date}}
        {{/if}}
      </a>
      <strong>
    </th>
  </tr>
  <tr>
    <th rowspan="2">Date</th>
    <th rowspan="2">libelle</th>
    <th colspan="{{$listRubriques|@count}}">Rubriques</th>
    <th rowspan="2">Mode</th>
    <th rowspan="2">Remarques</th>
  </tr>
  <tr>
    {{foreach from=$listRubriques item=rubrique}}
    <th>{{$rubrique->nom}}</th>
    {{/foreach}}
  </tr>
  {{foreach from=$listGestionCab item=fiche}}
  <tr>
    <td>{{$fiche->date|date_format:$conf.date}}</td>
    <td class="text">
      {{$fiche->libelle}} ({{$fiche->num_facture}})
    </td>
    {{foreach from=$listRubriques item=rubrique}}
    <td>
      {{if $rubrique->rubrique_id == $fiche->rubrique_id}}
      {{$fiche->montant|currency}}
      {{/if}}
    </td>
    {{/foreach}}
    <td>{{$fiche->_ref_mode_paiement->nom}}</td>
    <td class="text">{{$fiche->rques|nl2br}}</td>
  </tr>
  {{/foreach}}
  <tr>
    <th colspan="2">Totaux</th>
    {{foreach from=$listRubriques item=rubrique}}
    {{assign var="noTotal" value=1}}
      {{foreach from=$totaux item=curr_total}}
      {{if $rubrique->rubrique_id == $curr_total.rubrique_id}}
      <td>{{$curr_total.value|currency}}</td>
      {{assign var="noTotal" value=0}}
      {{/if}}
      {{/foreach}}
      {{if $noTotal}}
      <td>-</td>
      {{/if}}
    {{/foreach}}
    <th colspan="2">{{$total|currency}}</th>
</table>