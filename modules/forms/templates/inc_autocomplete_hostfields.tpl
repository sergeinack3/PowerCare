{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
	
{{foreach from=$host_fields item=element key=value}}
  <li data-prop="{{$element.prop}}" data-field="{{$element.field}}" data-value="{{$value}}" title="{{$element.longview}}">
  	<small style="float: right; color: #666;">
      {{$element.type}}
    </small>
    
    <span class="view" {{if !$show_views}} style="display: none;" {{/if}}>
      {{$element.view}}
    </span>
		
		<span style="{{if $show_views}} display: none; {{/if}} padding-left: {{$element.level}}em; {{if $element.level == 0}}font-weight: bold{{/if}}">
			{{$element.title}}
		</span>
  </li>
{{/foreach}}

</ul>