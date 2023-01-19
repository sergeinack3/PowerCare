{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=type_affectation value="soins UserSejour type_affectation"|gconf}}
{{foreach from=$sejours item=_sejour}}
  <tr>
    <td>
      <button type="button" class="mediuser_black notext"
              onclick="Soins.paramUserSejour('{{$_sejour->_id}}', '{{$service_id}}', 'callback', '{{$user_sejour->_debut}}');"
              {{if $_sejour->_ref_users_sejour|@count == 0}}style="opacity: 0.6;" {{/if}}></button>
      {{if $_sejour->_ref_users_sejour|@count}}
        <span class="countertip" style="margin-top: -2px;margin-left: -5px;">{{$_sejour->_count_users_sejour}}</span>
      {{/if}}
    </td>
    <td>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">
        {{$_sejour->_view}}
      </span>
      {{foreach from=$_sejour->_ref_users_sejour item=_user_sejour}}
        <br/>
        <button type="button" class="trash notext" onclick="PersonnelSejour.delUserMultiAffectation('{{$_user_sejour->_id}}')">
          {{tr}}Delete{{/tr}}
        </button>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_user_sejour->_ref_user}}
        {{if $type_affectation == "segment" && $_user_sejour->debut}}
          <span class="compact">
            <em>
              ({{tr}}date.From{{/tr}} {{$_user_sejour->debut|date_format:$conf.datetime}}
              {{tr}}date.to{{/tr}} {{$_user_sejour->fin|date_format:$conf.datetime}})
            </em>
          </span>
        {{/if}}
      {{/foreach}}
    </td>
  </tr>
{{foreachelse}}
  <tr>
    <td>
      <div class="small-warning">{{tr}}CSejour.none{{/tr}}</div>
    </td>
  </tr>
{{/foreach}}