{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<td>
    {{if $act->quantite > 1}}
        {{mb_value object=$act field=quantite}}&nbsp;x&nbsp;
    {{/if}}
    {{mb_value object=$act field=code}}
    {{if $act->coefficient}}
        &nbsp;{{mb_value object=$act field=coefficient}}
    {{/if}}
</td>
<td>
    {{if $act->complement}}
        <span class="circled" title="{{tr}}CActeNGAP.complement.{{$act->complement}}{{/tr}}">{{$act->complement}}</span>
    {{/if}}
</td>
<td>
    {{$act->execution|date_format:$conf.date}}
</td>
<td style="text-align: right;">
    {{mb_value object=$act field=montant_base}}
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
    {{mb_value object=$act field=_tarif}}
</td>
