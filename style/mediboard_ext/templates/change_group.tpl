{{*
 * @package Mediboard\Style\Mediboard
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $Etablissements|@count > 1}}
<form name="change-group" action="" method="get">
  <input type="hidden" name="m" value="{{$m}}" />
  <select name="g" onchange="this.form.submit()" {{if @$style}}style="{{$style}}"{{/if}}>
    {{foreach from=$Etablissements item=_group}}
      <option value="{{$_group->_id}}" {{if $_group->_id == $g}}selected="selected"{{/if}}>
        {{$_group}}
      </option>
    {{/foreach}}
  </select>   
</form>
{{else}}
  <strong>{{$Etablissements|@first}}</strong>
{{/if}}