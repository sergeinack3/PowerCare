{{*
 * @package Mediboard\Lpp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
    <th style="font-weight: bold; width: 120px;">{{mb_title class=CLPPDatedPricing field=begin_date}}</th>
    <td>{{mb_value object=$pricing field=begin_date}}</td>
</tr>
<tr>
    <th style="font-weight: bold; width: 120px;">{{mb_title class=CLPPDatedPricing field=end_date}}</th>
    <td>
        {{if $pricing->end_date}}
            {{mb_value object=$pricing field=end_date}}
        {{else}}
            <span class="empty">{{tr}}None{{/tr}}</span>
        {{/if}}
    </td>
</tr>
<tr>
    <th style="font-weight: bold; width: 120px;">{{mb_title class=CLPPDatedPricing field=prestation_code}}</th>
    <td>{{mb_value object=$pricing field=prestation_code}}</td>
</tr>
<tr>
    <th style="font-weight: bold; width: 120px;">{{mb_title class=CLPPDatedPricing field=price}}</th>
    <td>{{mb_value object=$pricing field=price}}</td>
</tr>
<tr>
    <th style="font-weight: bold; width: 120px;">{{mb_title class=CLPPDatedPricing field=dep}}</th>
    <td>{{mb_value object=$pricing field=dep}}</td>
</tr>
{{if $pricing->max_quantity}}
    <tr>
        <th style="font-weight: bold; width: 120px;">{{mb_title class=CLPPDatedPricing field=max_quantity}}</th>
        <td>{{mb_value object=$pricing field=max_quantity}}</td>
    </tr>
{{/if}}
{{if $pricing->max_price}}
    <tr>
        <th style="font-weight: bold; width: 120px;">{{mb_title class=CLPPDatedPricing field=max_price}}</th>
        <td>{{mb_value object=$pricing field=max_price}}</td>
    </tr>
{{/if}}
<tr>
    <th style="font-weight: bold; width: 120px;">{{mb_title class=CLPPDatedPricing field=maj_guadeloupe}}</th>
    <td>{{mb_value object=$pricing field=maj_guadeloupe}}</td>
</tr>
<tr>
    <th style="font-weight: bold; width: 120px;">{{mb_title class=CLPPDatedPricing field=maj_martinique}}</th>
    <td>{{mb_value object=$pricing field=maj_martinique}}</td>
</tr>
<tr>
    <th style="font-weight: bold; width: 120px;">{{mb_title class=CLPPDatedPricing field=maj_guyane}}</th>
    <td>{{mb_value object=$pricing field=maj_guyane}}</td>
</tr>
<tr>
    <th style="font-weight: bold; width: 120px;">{{mb_title class=CLPPDatedPricing field=maj_reunion}}</th>
    <td>{{mb_value object=$pricing field=maj_reunion}}</td>
</tr>
<tr>
    <th style="font-weight: bold; width: 120px;">{{mb_title class=CLPPDatedPricing field=maj_st_pierre_miquelon}}</th>
    <td>{{mb_value object=$pricing field=maj_st_pierre_miquelon}}</td>
</tr>
<tr>
    <th style="font-weight: bold; width: 120px;">{{mb_title class=CLPPDatedPricing field=maj_mayotte}}</th>
    <td>{{mb_value object=$pricing field=maj_mayotte}}</td>
</tr>
