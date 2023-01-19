{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main">
  <tr>
    <th colspan="2">
      <a href="#" onclick="window.print()">Rapport du {{$date|date_format:$conf.date}}</a>
    </th>
  </tr>
  <tr>
    <!-- Répartition des hospitalisés -->
    <td class="text">
      <table class="tbl">
        <tr>
          <th colspan="2">
            Répartition des hospitalisés présents par service
          </th>
        </tr>
        <tr>
          <th>Service</th>
          <th>Nombre</th>
        </tr>
        {{foreach from=$total_service item="service"}}
          <tr style="text-align: center">
            <td>{{$service.service->_view}}</td>
            <td>{{$service.total}}</td>
          </tr>
        {{/foreach}}
      </table>
    </td>
    <!-- Synthèse -->
    <td class="text">
      <table class="tbl" style="text-align: center">
        <tr>
          <th colspan="4">
            Synthèse hospi
          </th>
        </tr>
        <tr>
          <th style:
          "width=50px">Présents la veille</th>
          <td>{{$countPresentVeille}}</td>
        </tr>
        <tr>
          <th>Sorties du jour</th>
          <td>{{$countSortieJour}}</td>
        </tr>
        <tr>
          <th>Entrées du jour</th>
          <td>{{$countEntreeJour}}</td>
        </tr>
        <tr>
          <th>Présents du jour</th>
          <td>{{$countPresentJour}}</td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td colspan="2">
      <table class="tbl">
        <tr>
          <th>Médecins</th>
          <th>Hospitalisés</th>
          <th>Ambulatoires</th>
          <th>Total par médecins</th>
        </tr>
        {{foreach from=$totalPrat item="prat"}}
          {{if $prat.total}}
            <tr style="text-align: center">
              <td>{{$prat.prat->_view}}</td>
              <td>{{$prat.hospi}}</td>
              <td>{{$prat.ambu}}</td>
              <td>{{$prat.total}}</td>
            </tr>
          {{/if}}
        {{/foreach}}
        <tr>
          <th>Total</th>
          <th>{{$totalHospi}}</th>
          <th>{{$totalAmbulatoire}}</th>
          <th>{{$totalMedecin}}</th>
        </tr>
      </table>
    </td>
  </tr>
</table>