{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<th>{{tr}}CFormula-operateur{{/tr}}</th>
<td>
    <select name="operateur" id="operateur">
        {{foreach from=$operateurs key=code item=operateur}}
            <option value="{{$code}}">
                {{if $code !== '0'}}
                    {{tr}}CFormulaOperator-{{$code}}{{/tr}}
                {{else}}
                    {{tr}}None{{/tr}}
                {{/if}}
            </option>
        {{/foreach}}
    </select>
</td>
