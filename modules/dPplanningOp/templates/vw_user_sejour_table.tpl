{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div style="display: none" id="affectation_{{$sejour->_guid}}">
  <table class="tbl">
    {{if "soins UserSejour see_global_users"|gconf}}
      {{foreach from=$sejour->_ref_users_sejour item=_user}}
        <tr>
          <td>
            {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_user->_ref_user}}
            {{if "soins UserSejour type_affectation"|gconf == "segment"}}
              <span class="compact">
                <em>
                  (Du {{$_user->debut|date_format:$conf.datetime}} au {{$_user->fin|date_format:$conf.datetime}})
                </em>
              </span>
            {{/if}}
          </td>
        </tr>
      {{foreachelse}}
        <tr>
          <td class="empty">{{tr}}CUserSejour.none{{/tr}}</td>
        </tr>
      {{/foreach}}
    {{else}}
      {{foreach from=$sejour->_ref_users_by_type item=_users key=type}}
        <tr>
          <th>{{tr}}CUserSejour.{{$type}}{{/tr}}</th>
        </tr>
        {{foreach from=$_users item=_user}}
          <tr>
            <td>
              {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_user->_ref_user}}
              {{if "soins UserSejour type_affectation"|gconf == "segment"}}
                <span class="compact">
                <em>
                  (Du {{$_user->debut|date_format:$conf.datetime}} au {{$_user->fin|date_format:$conf.datetime}})
                </em>
              </span>
              {{/if}}
            </td>
          </tr>
          {{foreachelse}}
          <tr>
            <td class="empty">{{tr}}CUserSejour.none{{/tr}}</td>
          </tr>
        {{/foreach}}
      {{/foreach}}
    {{/if}}
  </table>
</div>