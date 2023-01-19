{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
    Main.add(function () {
        Control.Tabs.create('tabs-situations');
    });
</script>

<table class="form">
    <tr>
        <th class="title" colspan="2">
            {{mb_value object=$cps field=type_label}}
        </th>
    </tr>
    <tr>
        <th>{{mb_label object=$cps field=last_name}}</th>
        <td>
            {{mb_value object=$cps field=civility_label}} {{mb_value object=$cps field=last_name}} {{mb_value object=$cps field=first_name}}
        </td>
    </tr>
    <tr>
        <th>{{mb_label object=$cps field=national_identification_number}}
            ({{mb_value object=$cps field=national_identification_type_label}})
        </th>
        <td>{{mb_value object=$cps field=national_identification_number}}{{mb_value object=$cps field=national_identification_key}}</td>
    </tr>
    {{if $substitute}}
        <tr>
            <td colspan="2">
                {{mb_include module=jfse template=cps/substitute_session}}
            </td>
        </tr>
    {{/if}}
    <tr>
        <td colspan="2">
            <ul id="tabs-situations" class="control_tabs">
                {{foreach from=$cps->situations item=situation}}
                    <li><a
                          href="#situation-{{$situation->situation_id}}">{{tr}}CCpsSituation{{/tr}} {{$situation->situation_id}}</a>
                    </li>
                {{/foreach}}
            </ul>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            {{foreach from=$cps->situations item=situation}}
                <div id="situation-{{$situation->situation_id}}" style="display: none;">
                    {{mb_include module=jfse template=cps/situation}}
                </div>
            {{/foreach}}
        </td>
    </tr>
</table>
