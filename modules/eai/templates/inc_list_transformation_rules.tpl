{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=eai script=transformation_rule ajax=true}}

<div>
  <button onclick="EAITransformationRule.edit('0', '{{$transf_ruleset->_id}}');" class="button new">
    {{tr}}CTransformationRule-title-create{{/tr}}
  </button>

  <button onclick="EAITransformationRule.edit('', '{{$transf_ruleset->_id}}', true);" class="button duplicate">
    {{tr}}CTransformationRule-button-Duplicate all{{/tr}}
  </button>
</div>

<table class="main tbl">
  <tr>
    <th colspan="16" class="title">{{tr}}CTransformationRule.all{{/tr}}</th>
  </tr>
  <tr>
    <th class="section" colspan="8">CRuleSequence</th>
    <th class="section">Rule</th>
  </tr>
  <tr>
    <th class="narrow button"></th>
    <th class="category">{{mb_title class=CTransformationRule field=name}}</th>
    <th class="category narrow">{{mb_title class=CTransformationRule field=standard}}</th>
    <th class="category narrow">{{mb_title class=CTransformationRule field=domain}}</th>
    <th class="category narrow">{{mb_title class=CTransformationRule field=profil}}</th>
    <th class="category narrow">{{mb_title class=CTransformationRule field=transaction}}</th>
    <th class="category narrow">{{mb_title class=CTransformationRule field=message}}</th>
    <th class="category narrow">{{mb_title class=CTransformationRule field=version}}</th>
    <th class="category narrow">{{mb_title class=CTransformationRule field=extension}}</th>
    <th class="category">{{mb_title class=CTransformationRule field=xpath_source}}</th>
    <th class="category">{{mb_title class=CTransformationRule field=xpath_target}}</th>
    <th class="category narrow">{{mb_title class=CTransformationRule field=action_type}}</th>
    <th class="category narrow">{{mb_title class=CTransformationRule field=value}}</th>
    <th class="category narrow">{{mb_title class=CTransformationRule field=active}}</th>
    <th class="category narrow">{{mb_title class=CTransformationRule field=rank}}</th>
    <th class="category narrow">{{mb_title class=CTransformationRule field=_count_transformations}}</th>
  </tr>

  {{foreach from=$transf_rules item=_transformation_rule}}
    <tr {{if !$_transformation_rule->active}}class="opacity-30"{{/if}}>
      <td>
        <button class="button edit notext compact" onclick="EAITransformationRule.edit('{{$_transformation_rule->_id}}');"
                title="{{tr}}Edit{{/tr}}">
          {{tr}}Edit{{/tr}}
        </button>
        <button class="button notext compact duplicate" type="button" title="{{tr}}Duplicate{{/tr}}"
                onclick="EAITransformationRule.edit(
                  '{{$_transformation_rule->_id}}',
                  '{{$_transformation_rule->eai_transformation_ruleset_id}}', true)">
          {{tr}}Duplicate{{/tr}}
        </button>
      </td>
      <td class="text compact">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_transformation_rule->_guid}}');">
          {{mb_value object=$_transformation_rule field="name"}}
        </span>
      </td>
      <td class="text compact">{{mb_value object=$_transformation_rule field="standard"}}</td>
      <td class="text compact">{{mb_value object=$_transformation_rule field="domain"}}</td>
      <td class="text compact">{{mb_value object=$_transformation_rule field="profil"}}</td>
      <td class="text compact">{{mb_value object=$_transformation_rule field="transaction"}}</td>
      <td class="text compact">{{mb_value object=$_transformation_rule field="message"}}</td>
      <td class="text compact">{{mb_value object=$_transformation_rule field="version"}}</td>
      <td class="text compact">{{mb_value object=$_transformation_rule field="extension"}}</td>
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
      <td class="text compact">{{mb_value object=$_transformation_rule field="value"}}</td>
      <td class="text compact">{{mb_value object=$_transformation_rule field="active"}}</td>
      <td class="text compact">{{mb_value object=$_transformation_rule field="rank"}}
        <!-- Order -->
        <form name="formOrderRule-{{$_transformation_rule->_id}}" method="post"
          onsubmit="return onSubmitFormAjax(this,
            EAITransformationRuleSet.refreshTransformationRuleList.curry('{{$_transformation_rule->eai_transformation_ruleset_id}}'))">

          <input type="hidden" name="dosql" value="do_manage_transformation_rule" />
          <input type="hidden" name="m" value="eai" />
          <input type="hidden" name="ajax" value="1" />
          <input type="hidden" name="transformation_rule_id_move" value="{{$_transformation_rule->_id}}" />
          <input type="hidden" name="direction" value="" />

          <img src="./images/icons/updown.gif" usemap="#map-{{$_transformation_rule->_id}}" />
          <map name="map-{{$_transformation_rule->_id}}">
            <area coords="0,0,10,7"  href="#1" onclick="EAITransformationRule.sendForm(this);" />
            <area coords="0,8,10,14" href="#1" onclick="$V(this.up('form').direction, 'down');
              EAITransformationRule.moveRowDown(this.up('tr')); this.up('form').onsubmit();" />
          </map>
        </form>
      </td>
      <td class="button">
        <button onclick="EAITransformationRule.stats('{{$_transformation_rule->_id}}');" class="stats notext compact" type="button"
                title="{{tr}}Details{{/tr}}">
          {{tr}}Details{{/tr}}
        </button>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="16">{{tr}}CTransformationRule.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
