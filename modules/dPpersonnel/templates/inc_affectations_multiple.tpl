{{*
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=object_guid value=$_multiple.object->_guid}}
{{assign var=personnel_id value=$_multiple.personnel->_id}}
 
<tr>
  <td rowspan="{{$_multiple.affect_count}}">
  	<span onmouseover="ObjectTooltip.createEx(this, '{{$object_guid}}')">{{$_multiple.object}}</span>
  </td>
  <td rowspan="{{$_multiple.affect_count}}">
  	<span onmouseover="ObjectTooltip.createEx(this, '{{$_multiple.personnel->_guid}}')">{{$_multiple.personnel}}</span>
  </td>
  {{foreach from=$_multiple.affectations item=_affectation name=multiple}}
  {{assign var=class value=ok}}
  {{if !$_affectation->realise}}
    {{assign var=class value=warning}}
    {{if !$_affectation->debut && !$_affectation->debut}}
     {{assign var=class value=error}}
	{{/if}}
  {{/if}}
  <td>
  	{{mb_include module=system template=inc_object_idsante400 object=$_affectation}}
    {{mb_include module=system template=inc_object_history    object=$_affectation}}
  </td>
  <td>{{mb_value object=$_affectation field=realise}}</td>
  <td>{{mb_value object=$_affectation field=debut}}</td>
  <td>{{mb_value object=$_affectation field=fin}}</td>
  <td class="button {{$class}}">
    <button class="trash" onclick="deleteAffectation('{{$_affectation->_id}}', '{{$object_guid}}', '{{$personnel_id}}', '{{$class}}')">{{tr}}Delete{{/tr}}</button>
  </td>
{{if !$smarty.foreach.multiple.last}}
</tr>

<tr>
{{/if}}
	{{foreachelse}}
	<td colspan="10" class="empty">{{tr}}CAffectationPersonnel.none{{/tr}}</td>
  {{/foreach}}
</tr>
 
   