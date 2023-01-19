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
    <th rowspan="2">Type hospi</th>
    <th rowspan="2">CCAM</th>
    <th rowspan="2">Nombre d'interventions</th>
    <th colspan="2">Durée d'hospitalisation</th>
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
      <td>{{tr}}CSejour.type.{{$_temps->type}}{{/tr}}</td>
      <td>
        {{foreach from=$_temps->_codes item=_code}}
          <a class="action" href="#CodeCCAM-show-{{$_code}}" onclick="CodeCCAM.show('{{$_code}}')">
            {{$_code}}
          </a>
        {{/foreach}}
      </td>
      <td>{{$_temps->nb_sejour}}</td>
      <td>{{$_temps->duree_moy|string_format:"%.2f"}} jours</td>
      <td><i>{{if $_temps->duree_ecart}}{{$_temps->duree_ecart|string_format:"%.2f"}} jours{{else}}-{{/if}}</i></td>
    </tr>
  {{/foreach}}
  
  <tr>
    <th colspan="3">Total</th>
    <td>{{$total.nbSejours}}</td>
    <td>{{$total.duree_moy|string_format:"%.2f"}} jours</td>
    <td>-</td>
  </tr>
</table>