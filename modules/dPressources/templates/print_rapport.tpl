{{*
 * @package Mediboard\Ressources
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main">
  <tr>
    <th>
      <a href="#" onclick="window.print()">
        &mdash; Dr {{$prat->_view}} &mdash;<br />
        Plages du {{$filter->_date_min|date_format:$conf.longdate}}
        au {{$filter->_date_max|date_format:$conf.longdate}}<br />
        {{$plages|@count}}
        {{if $filter->paye}}
          plage(s) payée(s)
        {{else}}
          plage(s) en attente de paiement
        {{/if}}
        sur la periode
      </a>
    </th>
  </tr>
  <tr>
    <td>
      <table class="tbl">
        <tr>
          <th>Date</th>
          <th>Plage horaire</th>
          <th>Libellé</th>
          <th>Tarif</th>
        </tr>
        {{foreach from=$plages item=curr_plage}}
          <tr>
            <td>{{$curr_plage->date|date_format:$conf.longdate}}</td>
            <td>
              {{$curr_plage->debut|date_format:$conf.time}}
              &mdash;
              {{$curr_plage->fin|date_format:$conf.time}}
            </td>
            <td>{{$curr_plage->libelle}}</td>
            <td>{{$curr_plage->tarif|currency}}</td>
          </tr>
        {{/foreach}}
        <tr>
          <th colspan="2" />
          <th>Total</th>
          <td><strong>{{$total|currency}}</strong></td>
        </tr>
      </table>
    </td>
  </tr>
</table>