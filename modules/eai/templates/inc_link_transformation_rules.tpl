{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=event_name value=$event|getShortName}}

<form name="edit-{{$transformation->_guid}}" method="post" onsubmit="return EAITransformation.onSubmit(this)">
  <input type="hidden" name="m" value="eai" />
  <input type="hidden" name="dosql" value="do_link_transformation_aed" />

  <input type="hidden" name="actor_guid" value="{{$actor->_guid}}" />
  <input type="hidden" name="event_name" value="{{$event_name}}" />
  <input type="hidden" name="del" value="0" />

  <table class="main tbl">
    <tr>
      <th colspan="14" class="title">{{tr}}CTransformationRule.all{{/tr}}</th>
    </tr>
    <tr>
      <th class="narrow button"></th>
      <th class="category">{{mb_title class=CTransformationRule field=name}}</th>
      <th class="category">{{mb_title class=CTransformationRule field=xpath_source}}</th>
      <th class="category">{{mb_title class=CTransformationRule field=xpath_target}}</th>
      <th class="category narrow">{{mb_title class=CTransformationRule field=action_type}}</th>
      <th class="category narrow">{{mb_title class=CTransformationRule field=value}}</th>
    </tr>
    {{foreach from=$transf_rules item=_transformation_rule}}
      <tr {{if !$_transformation_rule->active}}class="opacity-30"{{/if}}>
        <td>
          <input type="checkbox" name="transformation_rules[{{$_transformation_rule->_id}}]" value="{{$_transformation_rule->_id}}"/>
        </td>
        <td class="text compact">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_transformation_rule->_guid}}');">
            {{mb_value object=$_transformation_rule field="name"}}
          </span>
        </td>
        <td class="text compact">
          {{if $_transformation_rule->xpath_source}}
            {{foreach from="|"|explode:$_transformation_rule->xpath_source item=_component}}
              <span class="circled">{{$_component}}</span>
            {{/foreach}}
          {{/if}}
        </td>
        <td class="text compact">
          {{if $_transformation_rule->xpath_target}}
            {{foreach from="|"|explode:$_transformation_rule->xpath_target item=_component}}
              <span class="circled">{{$_component}}</span>
            {{/foreach}}
          {{/if}}
        </td>
        <td class="button compact">
          <span class="transformation-{{$_transformation_rule->action_type}}"></span>
        </td>
        <td class="text compact">
          {{mb_value object=$_transformation_rule field="value"}}
        </td>
      </tr>
      {{foreachelse}}
      <tr>
        <td class="emtpy" colspan="14">{{tr}}CTransformationRule.none{{/tr}}</td>
      </tr>
    {{/foreach}}
    <tr>
      <td colspan="14" class="button">
        <button type="submit" class="modify">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
