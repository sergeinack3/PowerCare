{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=user_profile value=false}}

<table class="tbl">
  <thead>
    {{if $user_profile}}
      <tr>
        <th class="title" colspan="4" style="position: sticky;">
          {{tr}}CFavoriCsARR|pl{{/tr}}
        </th>
      </tr>
    {{/if}}
    <tr>
      <th style="position: sticky;">{{tr}}CActiviteCsARR-code{{/tr}}</th>
      <th style="position: sticky;">{{tr}}CActiviteCsARR-libelle{{/tr}}</th>
      {{if !$hide_selector}}
        <th style="position: sticky;"></th>
      {{/if}}
    </tr>
  </thead>
  <tbody>
    {{foreach from=$codes item=_code}}
      <tr class="alternate">
        <td>
          <button type="button" class="search notext" onclick="CsARR.viewActivite('{{$_code->code}}');">
            {{tr}}CActiviteCsARR-action-view_details{{/tr}}
          </button>
          {{$_code->code}}
        </td>
        <td>
          <span style="max-width: 90px; float: right; min-width: 65px;">
            {{if $_code->_occurrences}}
              <span title="{{tr}}CActiviteCsARR-_occurrences-desc{{/tr}}" class="csarr-occurrence circled">
                {{$_code->_occurrences}}
              </span>
            {{/if}}
            {{if ($user_profile && $user_profile->_id == $user->_id) || !$user_profile}}
              {{mb_include module=ssr template=csarr/inc_favori code=$_code float=false}}
            {{/if}}
          </span>
          <div class="text">
            {{$_code->libelle|smarty:nodefaults}}
          </div>
        </td>
        {{if !$hide_selector}}
          <td class="narrow">
            <button type="button" class="tick notext" onclick="CsARR.selectCode('{{$_code->code}}');" title="{{tr}}Select{{/tr}}">
              {{tr}}Select{{/tr}}
            </button>
          </td>
        {{/if}}
      </tr>
    {{foreachelse}}
      <tr>
        <td class="empty" colspan="4">
          {{tr}}CActiviteCsARR.none{{/tr}}
        </td>
      </tr>
    {{/foreach}}
  </tbody>
</table>