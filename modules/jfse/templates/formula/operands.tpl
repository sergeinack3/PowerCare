{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<table class="main tbl">
    <tr>
        <th>{{tr}}CFormulaOperand-code{{/tr}}</th>
        <th>{{tr}}CFormulaOperand-label{{/tr}}</th>
    </tr>
    {{foreach from=$operands key=code item=operand}}
        <tr>
            <td>{{$code}}</td>
            <td>{{$operand->label}}</td>
        </tr>
    {{/foreach}}
</table>

