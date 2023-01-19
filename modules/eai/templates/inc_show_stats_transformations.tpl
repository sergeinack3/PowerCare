{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="3"> {{$transf_rule->name}} </th>
  </tr>

  <tr>
    <th class="section">{{mb_title class=CTransformation field=actor_id}}</th>
    <th class="section">{{mb_title class=CTransformation field=standard}}</th>
    <th class="section">{{mb_title class=CTransformation field=domain}}</th>
  </tr>

  {{foreach from=$transf_rule->_ref_eai_transformations item=_transformation}}
    {{assign var=actor value=$_transformation->_ref_actor}}
    <tr>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$actor->_guid}}');">
           {{$actor->_view}}
         </span>
      </td>
      <td>{{$transf_rule->standard}}</td>
      <td>{{$transf_rule->domain}}</td>
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="3">
        {{tr}}CTransformationRuleSet-msg-transformation not link{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>
