{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<td>
    {{mb_value object=$act field=code_acte}}
    <span class="circled">
        {{mb_value object=$act field=code_activite}} - {{mb_value object=$act field=code_phase}}
    </span>
</td>
<td>
    {{foreach from=$act->_modificateurs item=modifier}}
      <span class="circled">{{$modifier}}</span>
    {{/foreach}}
</td>
<td>
    {{$act->execution|date_format:$conf.date}}
</td>
<td style="text-align: right;">
    {{mb_value object=$act field=_tarif}}
</td>
<td style="text-align: right;">
    {{mb_value object=$act field=montant_depassement}}
</td>
<td style="text-align: right;">
    {{if $act_view}}
        {{mb_value object=$act_view->pricing field=rate}}%
    {{/if}}
</td>
<td style="text-align: right;">
    {{if $act_view}}
        {{mb_value object=$act_view->pricing field=total_amo}}
    {{/if}}
</td>
<td style="text-align: right;">
    {{if $act_view}}
        {{mb_value object=$act_view->pricing field=total_amc}}
    {{/if}}
</td>
<td style="text-align: right;">
    {{mb_value object=$act field=_total}}
</td>
