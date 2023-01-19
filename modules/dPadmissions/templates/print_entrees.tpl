{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr class="clear">
    <th colspan="13">
      <h1>
        <a href="#" onclick="window.print()">
          Admissions {{tr}}CSejour.type.{{$type}}{{/tr}}
          du {{$date|date_format:$conf.longdate}} ({{$total}} admissions)
        </a>
      </h1>
    </th>
  </tr>
  {{if $group_by}}
    {{foreach from=$listByPrat key=key_prat item=curr_prat}}
      {{assign var="praticien" value=$curr_prat.praticien}}
      <tr class="clear">
        <td colspan="13">
          <h2>
            <strong>Dr {{$praticien->_view}}</strong>
            - {{$curr_prat.sejours|@count}} admission(s)
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
        <th>Entrée</th>
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
          <td class="text">
            {{$curr_sejour->_NDA_view}}
          </td>
          <td>
            <span onmouseover="ObjectTooltip.createEx(this, '{{$curr_sejour->_guid}}');">
              {{$curr_sejour->entree|date_format:$conf.time}}
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
            {{mb_include module=hospi template=inc_placement_sejour sejour=$curr_sejour}}
            ({{tr}}chambre_seule.{{$curr_sejour->chambre_seule}}{{/tr}})
          </td>
          <td class="text">{{mb_include template=inc_form_prestations sejour=$curr_sejour edit=1 with_button=0}}</td>
          <td class="text">{{$curr_sejour->rques}}</td>
        </tr>
      {{/foreach}}
    {{/foreach}}
  {{else}}
    <tr>
      <th colspan="4" class="title"><strong>Patient</strong></th>
      <th colspan="9" class="title"><strong>Sejour</strong></th>
    </tr>
    <tr>
      <th>Nom / Prenom</th>
      <th>Naissance (Age)</th>
      <th>Sexe</th>
      <th>Remarques</th>
      <th>Praticien</th>
      <th>NDA</th>
      <th>Entrée</th>
      <th>Type</th>
      <th>Dur.</th>
      <th>Conv.</th>
      <th>Chambre</th>
      <th>Prest.</th>
      <th>Remarques</th>
    </tr>
    {{foreach from=$sejours item=curr_sejour}}
      <tr>
        <td>
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
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$curr_sejour->_ref_praticien}}
        </td>
        <td class="text">
          {{$curr_sejour->_NDA_view}}
        </td>
        <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$curr_sejour->_guid}}');">
            {{$curr_sejour->entree|date_format:$conf.time}}
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
          {{mb_include module=hospi template=inc_placement_sejour sejour=$curr_sejour}}
          ({{tr}}chambre_seule.{{$curr_sejour->chambre_seule}}{{/tr}})
        </td>
        <td class="text">{{mb_include template=inc_form_prestations sejour=$curr_sejour edit=1 with_button=0}}</td>
        <td class="text">{{$curr_sejour->rques}}</td>
      </tr>
    {{/foreach}}
  {{/if}}
</table>
