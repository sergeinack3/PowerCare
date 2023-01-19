{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
    <tr>
        <th colspan="2">{{tr}}CConventionType-all{{/tr}}</th>
    </tr>
    <tr>
        <td>{{mb_label class=CConventionType field=code}}</td>
        <td>{{mb_label class=CConventionType field=label}}</td>
    </tr>
    {{foreach from=$types_convention item=type_convention}}
        <tr>
            <td>{{mb_value object=$type_convention field=code}}</td>
            <td>{{mb_value object=$type_convention field=label}}</td>
        </tr>
    {{/foreach}}
</table>
