{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{assign var=list_func value='Ox\Core\CMbMath::getListFunction'|static_call:""}}
<script>
  Main.add(function () {
    constantSpec.changeCat();
  });
</script>

<tr>
  <th>{{mb_label object=$spec field=formule}}</th>
  <td colspan="10">{{mb_field object=$spec field=_view_formule readonly="readonly"}}</td>
</tr>

<tr>
  <td>
    {{mb_field object=$spec field=category onchange="constantSpec.changeCat();"}}
  </td>
  <td id="constant_by_cat">
    {{foreach from=$by_category key=_cat item=_specs_by_cat}}
      <select name="select_cat_{{$_cat}}" class="hidden">
        {{foreach from=$_specs_by_cat item=_spec}}
          <option value="{{$_spec->name}}">{{$_spec->name}}</option>
        {{/foreach}}
      </select>
    {{/foreach}}
  </td>
  <td colspan="10">
    <button type="button" class="notext add"></button>
  </td>
</tr>

<tr>
  <td colspan="3">
    <table class="table">
      {{assign var=index value=0}}
      {{foreach from=$list_func.math item=_func}}
        {{if $index == 0}}
          <tr>
        {{/if}}
        <td><button type="button" class="button">{{$_func}}</button></td>
        {{if $index > 1}}
          </tr>
          {{assign var=index value=0}}
        {{else}}
          {{assign var=index value=$index+1}}
        {{/if}}
      {{/foreach}}
    </table>
  </td>
  <td>
    <table class="table">

      <tr>
        <td><button type="button" class="button" onclick="constantSpec.calculAddNumber(7)">7</button></td>
        <td><button type="button" class="button" onclick="constantSpec.calculAddNumber(8)">8</button></td>
        <td><button type="button" class="button" onclick="constantSpec.calculAddNumber(9)">9</button></td>
        <td><button type="button" class="button" onclick="constantSpec.calculAddOp('-')">-</button></td>
      </tr>
      <tr>
        <td><button type="button" class="button" onclick="constantSpec.calculAddNumber(4)">4</button></td>
        <td><button type="button" class="button" onclick="constantSpec.calculAddNumber(5)">5</button></td>
        <td><button type="button" class="button" onclick="constantSpec.calculAddNumber(6)">6</button></td>
        <td><button type="button" class="button" onclick="constantSpec.calculAddOp('+')">+</button></td>
      </tr>
      <tr>
        <td><button type="button" class="button" onclick="constantSpec.calculAddNumber(1)">1</button></td>
        <td><button type="button" class="button" onclick="constantSpec.calculAddNumber(2)">2</button></td>
        <td><button type="button" class="button" onclick="constantSpec.calculAddNumber(3)">3</button></td>
        <td><button type="button" class="button" onclick="constantSpec.calculAddOp('x')">x</button></td>
      </tr>
      <tr>
        <td><button type="button" class="button" onclick="constantSpec.calculAddParenthese('(')">(</button></td>
        <td><button type="button" class="button" onclick="constantSpec.calculAddNumber(0)">0</button></td>
        <td><button type="button" class="button" onclick="constantSpec.calculAddParenthese(')')">)</button></td>
        <td><button type="button" class="button" onclick="constantSpec.calculAddOp('/')">/</button></td>
      </tr>
    </table>
  </td>
</tr>