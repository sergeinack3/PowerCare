{{*
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th rowspan="2">Praticien</th>
    <th rowspan="2">Nombre de préparation</th>
    <th rowspan="2">Nombre de plages</th>
    <th colspan="2">Durée des pauses</th>
  </tr>
  <tr>
    <th>Moyenne</th>
    <th>Ecart-type</th>
  </tr>
  {{foreach from=$listTemps item=_temps}}
    <tr>
      <td>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_temps->_ref_praticien}}
      </td>
      <td>{{$_temps->nb_prepa}}</td>
      <td>{{$_temps->nb_plages}}</td>
      <td>{{$_temps->duree_moy|date_format:"%Mmin %Ss"}}</td>
      <td>{{$_temps->duree_ecart|date_format:"%Mmin %Ss"}}</td>
    </tr>
  {{/foreach}}
  <tr>
    <th>Total</th>
    <td>{{$total.nbPrep}}</td>
    <td>{{$total.nbPlages}}</td>
    <td>{{if $total.moyenne}}{{$total.moyenne|date_format:"%Mmin %Ss"}}{{else}}-{{/if}}</td>
    <td>-</td>
  </tr>

</table>