{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
    <tr>
        <th>{{tr}}CFormula-label{{/tr}}</th>
        <th>{{tr}}CFormula-formula_number{{/tr}}</th>
        <th>{{tr}}CFormula-pmss{{/tr}}</th>
        <th>{{tr}}CFormula-prestation_number{{/tr}}</th>
        <th>{{tr}}CFormula-sts{{/tr}}</th>
        <th>{{tr}}CFormula-theorical_calculation{{/tr}}</th>
        <th>{{tr}}CFormula-parameters{{/tr}}</th>
    </tr>
    {{foreach from=$formulas item=formula}}
        <tr>
            <td>{{$formula->label}}</td>
            <td>{{$formula->formula_number}}</td>
            <td>{{$formula->pmss}}</td>
            <td>{{$formula->prestation_number}}</td>
            <td><i class="me-icon {{if $formula->sts}}tick{{else}}close{{/if}}"></i></td>
            <td>{{$formula->theorical_calculation}}</td>
            <td>
                {{mb_include module=Jfse template=formula/formula_parameters params=$formula->parameters}}
            </td>
        </tr>
    {{/foreach}}
</table>
