{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="class-selection" action="" method="get">
	<input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="tab" value="{{$tab}}" />
	
	<label for="class">Classe</label>
	<select name="class" onchange="this.form.submit()">
		{{foreach from=$classes item=_class}}
		  <option value="{{$_class}}" {{if $class == $_class}} selected="selected" {{/if}}>
		  	{{$_class}} - {{tr}}{{$_class}}{{/tr}}
			</option>
		{{/foreach}}
	</select>
	
	<label for="count">Nombre d'objets à afficher</label>
  <input type="text" name="count" value="{{$objects_count}}" size="4" />
</form>

<table class="tbl main">
	<tr>
		<th>Collection</th>
		<th>Violations</th>
	</tr>
	{{foreach from=$zombies key=name item=zombie}}
	  <tr>
			<td style="font-weight: bold;">
        {{assign var=backSpec value=$object->_backSpecs[$name]}}
        {{assign var=initiator value=$backSpec->_initiator}}
			  {{tr}}{{$initiator}}-back-{{$name}}{{/tr}}
			</td>
			
			<td>
				<div class="{{$zombie.count|ternary:warning:info}}">
					{{if $backSpec->_unlink}}<strong>Unlink</strong> :{{/if}}
					<span onmouseover="ObjectTooltip.createDOM(this, '{{$_class}}-{{$name}}')">
						{{$zombie.count}}
					</span>
				</div>
				
				<table class="tbl" id="{{$_class}}-{{$name}}" style="display: none;">
				  <tr>
				  	<th>{{$initiator}}.{{$name}}</th>
					</tr>
	        {{foreach from=$zombie.objects item=_object}}
          <tr>
            <td>
		          <span onmouseover="ObjectTooltip.createEx(this, '{{$_object->_guid}}')">
		            {{$_object}}
		          </span>
            </td>
          </tr>
          {{foreachelse}}
					<tr><td class="empty">{{tr}}None{{/tr}}</tr>
	        {{/foreach}}
        </table>
			</td>

		</tr>
	{{foreachelse}}
	<tr><td colspan="3">La classe n'est référencé dans aucune collection</td></tr>
	{{/foreach}}
</table>