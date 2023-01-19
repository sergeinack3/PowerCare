{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}


{{*<select id="banque_id" name="banque_id" style="width: 150px;" onchange="BanqueEdit.editButton(this.value)">*}}
{{*    <option value="">&mdash; {{tr}}CBanque.select{{/tr}}</option>*}}
{{*    {{foreach from=$banques item="banque"}}*}}
{{*        <option value="{{$banque->_id}}"*}}
{{*                {{if $app->_ref_user->banque_id == $banque->_id}}selected="selected"{{/if}}>*}}
{{*            {{$banque->_view}}*}}
{{*        </option>*}}
{{*    {{/foreach}}*}}
{{*</select>*}}

<ul>
    <li data-banque-id="">
        <div class="empty view">&mdash; {{tr}}CBanque.select{{/tr}}</div>
    </li>
    {{foreach from=$banques item="banque"}}
        <li data-banque-id="{{$banque->_id}}">
            <div>{{if $app->_ref_user->_group_id == $banque->group_id}}<i class="far fa-hospital event-icon sejour-avenir me-event-icon"></i>{{/if}} <span class="view">{{$banque->_view}}</span></div>
        </li>
    {{/foreach}}
</ul>
