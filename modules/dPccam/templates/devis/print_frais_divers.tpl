{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=total value=0}}

<table class="tbl">
  <tr>
    <th>Quantite</th>
    <th>Code</th>
    <th>Libelle</th>
    <th>Coefficient</th>
    <th>Montant</th>
  </tr>
  {{foreach from=$devis->_ref_frais_divers item=_act}}
    <tr>
      <td>
        {{mb_value object=$_act field=quantite}}
      </td>
      <td>
        {{mb_value object=$_act->_ref_type field=code}}
      </td>
      <td>
        {{mb_value object=$_act->_ref_type field=libelle}}
      </td>
      <td>
        {{mb_value object=$_act field=coefficient}}
      </td>
      <td>
        {{mb_value object=$_act field=_montant}}
      </td>
    </tr>
    {{math assign=total equation="x+y" x=$total y=$_act->_montant}}
  {{/foreach}}
  <tr>
    <th>Total</th>
    <th colspan="3"></th>
    <th style="text-align: right;">{{$total|currency}}</th>
  </tr>
</table>