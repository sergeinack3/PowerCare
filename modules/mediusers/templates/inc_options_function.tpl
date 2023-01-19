{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=selected value=null}}
{{mb_default var=disabled value=null}}

{{foreach from=$list item=_function}}
  {{assign var=color value=$_function->color}}
  <option class="mediuser" 
          style="border-color: #{{$color}};" 
          value="{{$_function->_id}}" 
          {{if $selected == $_function->_id}}selected{{/if}}
          {{if $disabled == $_function->_id}}disabled{{/if}}>
    {{$_function}}
  </option>
{{foreachelse}}
  {{if @$showEmptyList}}
  <option disabled>{{tr}}CFunctions.none{{/tr}}</option>
  {{/if}}
{{/foreach}}