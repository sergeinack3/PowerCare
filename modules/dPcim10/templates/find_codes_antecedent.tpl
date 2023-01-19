{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $version == 'atih' || $version == 'gm'}}
  {{if $cim10->_descendants|@count}}
    <td class="text">
      <label>
        <input type="radio" name="codecim" value="{{$cim10->code}}" onchange="{{$callback}}" />
        <strong>{{$cim10->code}}</strong>: <span title="{{$cim10->libelle}}">{{$cim10->libelle}}</span>
      </label><br/>
      {{foreach from=$cim10->_descendants item=curr_level}}
        <label>
          <input type="radio" name="codecim" value="{{$curr_level->code}}" onchange="{{$callback}}" />
          <strong>{{$curr_level->code}}</strong>: <span title="{{$curr_level->libelle}}">{{$curr_level->libelle}}</span>
        </label><br/>
      {{/foreach}}
    </td>
  {{/if}}
{{else}}
  {{if $cim10->_levelsInf|@count}}
    <td class="text">
      <label>
        <input type="radio" name="codecim" value="{{$cim10->code}}" onchange="{{$callback}}" />
        <strong>{{$cim10->code}}</strong>: <span title="{{$cim10->libelle}}">{{$cim10->libelle}}</span>
      </label><br/>
      {{foreach from=$cim10->_levelsInf item=curr_level}}
        {{if $curr_level->sid != 0}}
          <label>
            <input type="radio" name="codecim" value="{{$curr_level->code}}" onchange="{{$callback}}" />
            <strong>{{$curr_level->code}}</strong>: <span title="{{$curr_level->libelle}}">{{$curr_level->libelle}}</span>
          </label><br/>
        {{/if}}
      {{/foreach}}
    </td>
  {{/if}}
{{/if}}


