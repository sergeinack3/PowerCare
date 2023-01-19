{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=jfse script=Cps ajax=true}}

<div id="jfse-container">
    <table class="tbl">
        <tr>
            <th class="title" colspan="4">{{tr}}CCpsCard-title-select_situation{{/tr}}</th>
        </tr>
        <tr>
            <th colspan="4">CPS {{mb_value object=$cps field=last_name}} {{mb_value object=$cps field=first_name}}</th>
        </tr>
        {{foreach from=$cps->situations item=situation}}
            <tr>
                <td>{{mb_value object=$situation field=structure_name}}</td>
                <td>{{mb_value object=$situation field=invoicing_number}}{{mb_value object=$situation field=invoicing_number_key}}</td>
                <td>{{mb_value object=$situation field=speciality_label}} ({{mb_value object=$situation field=speciality_code}})</td>
                <td><button type="button" class="tick" onclick="Cps.selectSituation('{{$situation->situation_id}}', '{{$callback_route}}');">{{tr}}Select{{/tr}}</button></td>
            </tr>
        {{/foreach}}
    </table>
</div>
