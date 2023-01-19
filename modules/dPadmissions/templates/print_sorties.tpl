{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr class="clear">
    <th colspan="11">
      <h1>
        <a href="#" onclick="window.print()">
          Sorties {{tr}}CSejour.type.{{$type}}{{/tr}}
          du {{$date|date_format:$conf.longdate}} ({{$total}} sorties)
        </a>
      </h1>
    </th>
  </tr>
  {{foreach from=$listByPrat key=key_prat item=curr_prat}}
    {{assign var="praticien" value=$curr_prat.praticien}}
    <tr class="clear">
      <td colspan="11">
        <h2>
          <strong>Dr {{$praticien->_view}}</strong>
          - {{$curr_prat.sejours|@count}} sortie(s)
        </h2>
      </td>
    </tr>
    <tr>
      <th colspan="4"><strong>Patient</strong></th>
      <th colspan="8"><strong>Sejour</strong></th>
    </tr>
    <tr>
      <th>Nom / Prenom</th>
      <th>Naissance (Age)</th>
      <th>Sexe</th>
      <th>Remarques</th>
      <th>NDA</th>
      <th>Sortie</th>
      <th>Type</th>
      <th>Dur.</th>
      <th>Conv.</th>
      <th>Chambre</th>
      <th>Prest.</th>
      <th>Remarques</th>
    </tr>
    {{foreach from=$curr_prat.sejours item=curr_sejour}}
      <tr>
        <td class="text">
      <span onmouseover="ObjectTooltip.createEx(this, '{{$curr_sejour->_ref_patient->_guid}}');">
        {{$curr_sejour->_ref_patient->_view}}
      </span>
        </td>
        <td>
          {{mb_value object=$curr_sejour->_ref_patient field="naissance"}} ({{$curr_sejour->_ref_patient->_age}})
        </td>
        <td>
          {{$curr_sejour->_ref_patient->sexe}}
        </td>
        <td class="text">
          {{$curr_sejour->_ref_patient->rques}}
        </td>
        <td>
          {{$curr_sejour->_NDA_view}}
        </td>
        <td>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$curr_sejour->_guid}}');">
        {{$curr_sejour->sortie|date_format:$conf.time}}
      </span>
        </td>
        <td>
          {{if !$curr_sejour->facturable}}
            <strong>NF</strong>
          {{/if}}

          {{$curr_sejour->type|truncate:1:""|capitalize}}
        </td>
        <td>{{$curr_sejour->_duree_prevue}} j</td>
        <td class="text">{{$curr_sejour->convalescence|nl2br}}</td>
        <td class="text">
          {{assign var="affectation" value=$curr_sejour->_ref_last_affectation}}
          {{if $affectation->affectation_id}}
            {{$affectation->_ref_lit->_view}}
          {{else}}
            Non placé
          {{/if}}
          ({{tr}}chambre_seule.{{$curr_sejour->chambre_seule}}{{/tr}})
        </td>
        <td class="text">{{$curr_sejour->_ref_prestation->_view}}</td>
        <td class="text">{{$curr_sejour->rques}}</td>
      </tr>
    {{/foreach}}
  {{/foreach}}
</table>