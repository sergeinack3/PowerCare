{{*
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ccam script=code_ccam}}

<table class="tbl">
  <tr>
    <th rowspan="2">Praticien</th>
    <th rowspan="2">Codes CCAM</th>
    <th rowspan="2">Nombre <br />d'interventions</th>
    <th rowspan="2">Estimation de durée</th>
    <th colspan="2">Occupation de salle</th>
    <th colspan="2">Durée d'intervention</th>
    <th colspan="2">Durée salle de reveil</th>
  </tr>
  <tr>
    <th>Moyenne</th>
    <th>Ecart-type</th>
    <th>Moyenne</th>
    <th>Ecart-type</th>
    <th>Moyenne</th>
    <th>Ecart-type</th>
  </tr>
  {{foreach from=$listTemps item=_temps}}
    <tr>
      <td>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_temps->_ref_praticien}}
      </td>
      <td class="text">
        {{foreach from=$_temps->_codes item=_code}}
          <a class="action" href="#CodeCCAM-show-{{$_code}}" onclick="CodeCCAM.show('{{$_code}}')">
            {{$_code}}
          </a>
        {{/foreach}}
      </td>
      <td>{{$_temps->nb_intervention}}</td>
      {{if $_temps->estimation > $_temps->occup_moy}}
      <td style="background-color: #aaf;">
        {{elseif $_temps->estimation < $_temps->duree_moy}}
      <td style="background-color: #faa;">
        {{else}}
      <td style="background-color: #afa;">
        {{/if}}
        {{$_temps->estimation|date_format:$conf.time}}
      </td>
      <td>{{$_temps->occup_moy|date_format:$conf.time}}</td>
      <td><i>{{if $_temps->occup_ecart != "-"}}{{$_temps->occup_ecart|date_format:$conf.time}}{{else}}-{{/if}}</i></td>
      <td>{{$_temps->duree_moy|date_format:$conf.time}}</td>
      <td><i>{{if $_temps->duree_ecart != "-"}}{{$_temps->duree_ecart|date_format:$conf.time}}{{else}}-{{/if}}</i></td>
      <td>{{$_temps->reveil_moy|date_format:$conf.time}}</td>
      <td><i>{{if $_temps->reveil_ecart != "-"}}{{$_temps->reveil_ecart|date_format:$conf.time}}{{else}}-{{/if}}</i></td>
    </tr>
  {{/foreach}}
  
  <tr>
    <th colspan="2">Total</th>
    <td>{{$total.nbInterventions}}</td>
    {{if $total.nbInterventions}}
      {{if $total.estim_moy > $total.occup_moy}}
        <td style="background-color: #aaf;">
          {{elseif $total.estim_moy < $total.duree_moy}}
        <td style="background-color: #faa;">
          {{else}}
        <td style="background-color: #afa;">
      {{/if}}
      {{$total.estim_moy|date_format:$conf.time}}
      </td>
      <td>{{$total.occup_moy|date_format:$conf.time}}</td>
      <td>-</td>
      <td>{{$total.duree_moy|date_format:$conf.time}}</td>
      <td>-</td>
      <td>{{$total.reveil_moy|date_format:$conf.time}}</td>
      <td>-</td>
    {{else}}
      <td class="emtpy">-</td>
      <td class="emtpy">-</td>
      <td class="emtpy">-</td>
      <td class="emtpy">-</td>
      <td class="emtpy">-</td>
      <td class="emtpy">-</td>
      <td class="emtpy">-</td>
    {{/if}}
  </tr>
</table>