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
    <span class="circled">
        {{mb_value object=$act field=code_prestation}}
    </span>
</td>
<td></td>
<td>
    {{$act->execution|date_format:$conf.date}}
</td>
<td style="text-align: right;">
    {{mb_value object=$act field=montant_final}}
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
    {{mb_value object=$act field=montant_total}}
</td>
