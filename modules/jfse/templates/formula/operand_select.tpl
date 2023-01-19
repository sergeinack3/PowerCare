{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=$field value="operande1"}}
<th>{{tr}}CFormula-{{$field}}{{/tr}}</th>
<td>
    <select name="{{$field}}" id="{{$field}}">
        {{foreach from=$operands key=code item=operand}}
            <option value="{{$operand->code}}">{{$operand->label}}</option>
        {{/foreach}}
    </select>
</td>

