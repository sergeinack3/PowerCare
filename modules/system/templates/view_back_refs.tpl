{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main">
  <tr>
    <td style="width: 0.1px; white-space: nowrap;">
	 	 	
    <script type="text/javascript">
    Main.add(function () {
      Control.Tabs.create('tabs-backs');
    });
    </script>
    
    <ul id="tabs-backs" class="control_tabs_vertical">
      {{foreach from=$object->_back key=backName item=backObjects}}
      {{assign var=backSpec value=$object->_backSpecs.$backName}}
      {{assign var=count value=$counts.$backName}}
      <li >
        <a href="#back-{{$backName}}" {{if !$count}}class="empty"{{/if}}>
          {{tr}}{{$backSpec->_initiator}}-back-{{$backName}}{{/tr}}
					{{if $count}}
          <small>({{$count}})</small>
          {{/if}}
        </a>
      </li>
      {{/foreach}}
    </ul>

	 	</td>
		
		<td>
			
<table class="tbl">

<tr>
{{foreach from=$objects item=_object}}
  <th class="title" style="width: 50%">
    {{$_object->_view}}
  </th>
{{/foreach}}
</tr>

{{foreach from=$object->_back key=backName item=backObjects}}
{{assign var=backSpec value=$object->_backSpecs.$backName}}
<tbody id="back-{{$backName}}">
  
<tr>
  {{foreach from=$objects item=_object}}
  <th class="category">
    {{tr}}{{$backSpec->_initiator}}-back-{{$backName}}{{/tr}}
    {{if $_object->_count.$backName}}
    ( x {{$_object->_count.$backName}})
    {{/if}}
  </th>
  {{/foreach}}
</tr>

<tr>
{{foreach from=$objects item=_object}}
  <td style="vertical-align: top;">
  {{foreach from=$_object->_back.$backName item=backRef}}
    <span onmouseover="ObjectTooltip.createEx(this, '{{$backRef->_guid}}')">
      {{$backRef->_view}}
    </span>
    <br />
  {{foreachelse}}
  <div class="empty">Aucun objet</div>
  {{/foreach}}
  {{if $_object->_count.$backName != count($_object->_back.$backName)}}
  ...
  {{/if}}
  </td>
{{/foreach}}
</tr>

</tbody>
{{/foreach}}

</table>
			
		</td>
	</tr>
</table>	 
