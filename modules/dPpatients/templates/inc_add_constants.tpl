{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $_constant->value_class|get_parent_class == "CInterval"}}
  <script type="text/javascript">
    Main.add(function () {
      let item  = document.getElementById('constant_min_{{$_constant->_id}}');
      let item2 = document.getElementById('constant_max_{{$_constant->_id}}');
      if (item && item2) {
        Calendar.regField(item);
        Calendar.regField(item2);
      }
    })
  </script>
{{/if}}

<th class="category">{{$_constant->getViewName()}}</th>
<td class="narrow">
  {{if $_constant->value_class|get_parent_class == "CInterval"}}
    <input type="{{$_constant->_input_type}}" class="dateTime" id="constant_min_{{$_constant->_id}}" name="constant_min_{{$_constant->_id}}"> ||
    <input type="{{$_constant->_input_type}}" class="dateTime" id="constant_max_{{$_constant->_id}}" name="constant_max_{{$_constant->_id}}">
    {{if $_constant->value_class == "CStateInterval"}}
      ||
      <input type="text" name="constant_{{$_constant->_id}}">
    {{/if}}
  {{else}}
    <input class="narrow" type="{{$_constant->_input_type}}" name="constant_{{$_constant->_id}}">
    {{if $_constant->_is_constant_base}}{{$_constant->unit}}{{else}}{{tr}}CConstantSpec.unit.{{$_constant->unit}}|pl{{/tr}}{{/if}}
  {{/if}}
</td>
