{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=message_name value=""}}
{{mb_default var=event_name value=""}}
{{mb_default var=readonly value=false}}

{{foreach from=$transformations item=_transformation}}
  {{assign var=transformation_rule_sequence value=$_transformation->_ref_transformation_rule_sequence}}

  <tr {{if !$_transformation->active}}class="opacity-30"{{/if}}>
    <td class="text compact">{{mb_value object=$transformation_rule_sequence field=standard}}</td>
    <td class="text compact">{{mb_value object=$transformation_rule_sequence field=domain}}</td>
    <td class="text compact">{{mb_value object=$transformation_rule_sequence field=profil}}</td>
    <td class="text compact">{{mb_value object=$transformation_rule_sequence field=message_type}}</td>
    <td class="text compact">{{mb_value object=$transformation_rule_sequence field=transaction}}</td>
    <td>{{mb_value object=$transformation_rule_sequence field=version}}</td>
    <td>{{mb_value object=$transformation_rule_sequence field=extension}}</td>
    <td class="button compact">{{tr}}CTransformationRule-action-{{$_transformation->action_type}}{{/tr}}</td>
    <td class="button compact">{{mb_value object=$_transformation field="xpath_source"}}</td>
    <td class="button compact">{{mb_value object=$_transformation field="xpath_target"}}</td>
  </tr>
  {{foreachelse}}
  <tr><td class="empty" colspan="14">{{tr}}CTransformation.none{{/tr}}</td></tr>
{{/foreach}}
