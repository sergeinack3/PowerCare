{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$params key=key item=parameter name=parameters}}
    {{if $smarty.foreach.parameters.index === '1' || $smarty.foreach.parameters.index === '3'}}
        {{* Affiche les opérandes *}}
        {{if $parameter.valeur !== "-1"}}
            {{$operands[$smarty.foreach.parameters.index]->label}}
        {{else}}
            {{tr}}CFormulaOperand-no-operand{{/tr}}
        {{/if}}
        <br>
    {{elseif $smarty.foreach.parameters.index === '2'}}
        {{* Affiche l'opérateur *}}
        {{tr}}CFormulaOperator-{{$parameter.valeur}}{{/tr}}
        <br>
    {{else}}
        <span>{{$parameter.libelle}} : </span>
        <span>
            {{if $smarty.foreach.parameters.index === '4'}}
                {{$parameter.valeur|currency}}
            {{else}}
                {{$parameter.valeur}}
            {{/if}}
        </span>
        <br>
    {{/if}}
{{/foreach}}
