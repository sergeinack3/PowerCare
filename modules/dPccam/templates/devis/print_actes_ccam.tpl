{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=montant_base value=0}}
{{assign var=depassement value=0}}
{{assign var=total value=0}}

<table class="tbl" style="font: inherit;">
  <tr>
    <th>Code</th>
    <th>Modificateurs</th>
    <th>Montant base</th>
    <th>Dépassement</th>
    <th>Total</th>
  </tr>
  {{foreach from=$devis->_ref_actes_ccam item=_act}}
    <tr>
      <td>
        {{$_act->code_acte}} <span class="circled">{{$_act->code_activite}} - {{$_act->code_phase}}</span>
      </td>
      <td>
        {{foreach from=$_act->_modificateurs item=_modif}}
          <span class="circled">{{$_modif}}</span>
        {{/foreach}}
      </td>
      <td style="text-align: right;">
        {{mb_value object=$_act field=_tarif}}
        {{math assign=montant_base equation="x+y" x=$montant_base y=$_act->_tarif}}
      </td>
      <td style="text-align: right;">
        {{if $_act->montant_depassement}}
          {{math assign=depassement equation="x+y" x=$depassement y=$_act->montant_depassement}}
          {{mb_value object=$_act field=montant_depassement}}
        {{else}}
          {{'0'|currency}}
        {{/if}}
      </td>
      <td style="text-align: right;">
        {{mb_value object=$_act field=_total}}
      </td>
    </tr>
    {{math assign=total equation="x+y" x=$total y=$_act->_total}}
  {{/foreach}}
  <tr>
    <th>Total</th>
    <th></th>
    <th style="text-align: right;">{{$montant_base|currency}}</th>
    <th style="text-align: right;">{{$depassement|currency}}</th>
    <th style="text-align: right;">{{$total|currency}}</th>
  </tr>
</table>