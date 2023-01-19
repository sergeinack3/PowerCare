{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=selected_formula value=null}}
{{assign var=selected_formula_number value='052'}}
{{if $selected_formula}}
    {{assign var=selected_formula_number value=$selected_formula->formula_number}}
{{/if}}

<tr>
    <td colspan="4" class="button">
        <select name="formula" onchange="ThirdPartyPayment.onChangeFormula(this);">
            <option value="">&mdash; {{tr}}Select{{/tr}}</option>
            {{foreach from=$formulas item=formula}}
                <option value="{{$formula->formula_number}}"{{if $formula->formula_number == $selected_formula_number || count($formulas) === 1}} selected="selected"{{/if}}>
                    {{$formula->formula_number}} &mdash; {{$formula->label}} ({{$formula->theoretical_calculation}})
                </option>
            {{/foreach}}
        </select>
    </td>
</tr>
{{foreach from=$formulas item=formula}}
    {{if count($formula->parameters)}}
        <tbody class="formulas_parameter_container" id="formula_{{$formula->formula_number}}_parameters"{{if $formula->formula_number != $selected_formula_number}} style="display: none;"{{/if}}>
        {{foreach from=$formula->parameters item=parameter name=formulas_parameter}}
            {{assign var=parameter_value value=$parameter->value}}
            {{if $selected_formula_number == $formula->formula_number}}
                {{foreach from=$selected_formula->parameters item=_selected_formula_parameter}}
                    {{if $_selected_formula_parameter->number == $parameter->number}}
                        {{assign var=parameter_value value=$_selected_formula_parameter->value}}
                    {{/if}}
                {{/foreach}}
            {{/if}}
            {{if $smarty.foreach.formulas_parameter.iteration % 2 !== 0}}
                <tr>
            {{/if}}
            {{me_form_field nb_cells=1 layout=true label=$parameter->label}}
                <input type="number" name="formula_{{$formula->formula_number}}_parameter_{{$parameter->number}}" value="{{$parameter_value}}"{{if $parameter->type == 'P'}} min="0" max="100"{{/if}}
                       data-number="{{$parameter->number}}" data-type="{{$parameter->type}}" data-label="{{$parameter->label}}"/>
            {{if $parameter->type == 'P'}}%{{else}}{{$conf.currency_symbol|html_entity_decode}}{{/if}}
            {{/me_form_field}}
            {{if $smarty.foreach.formulas_parameter.iteration % 2 === 0}}
                </tr>
            {{elseif $smarty.foreach.formulas_parameter.last}}
                <td></td>
                </tr>
            {{/if}}
        {{/foreach}}
        </tbody>
    {{/if}}
{{/foreach}}
