{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <th colspan="6" class="section">{{tr}}{{$title}}{{/tr}}</th>
</tr>

{{if !$new_profile}}
  <tr>
    <th colspan="3" class="section"></th>
    <th class="section narrow">
      <input type="radio" name="check-import-{{$type}}" onclick="ImportUsers.checkAllRadio('profile-import-use-{{$type}}', 'old');" checked/>
    </th>
    <th class="section narrow">
      <input type="radio" name="check-import-{{$type}}" onclick="ImportUsers.checkAllRadio('profile-import-use-{{$type}}', 'new');"/>
    </th>
    <th class="section"></th>
  </tr>
{{/if}}

{{foreach from=$table key=_pref item=_value}}
  <tr>
    <th align="center" colspan="2">{{tr}}pref-{{$_pref}}{{/tr}}</th>

    {{if !$new_profile}}
      <td align="center" class="text{{if $_value.old == -1}}error{{/if}}">
        {{if $_value.old == -1}}
          {{tr}}CUser-import-pref-missing{{/tr}}
        {{else}}
          {{$_value.old}}
        {{/if}}
      </td>

      <td class="narrow" align="center">
        <input type="radio" name="use_{{$type}}_{{$_pref}}_prefs" class="profile-import-use-{{$type}}" value="old" checked/>
      </td>
      <td class="narrow" align="center">
        <input type="radio" name="use_{{$type}}_{{$_pref}}_prefs" class="profile-import-use-{{$type}}" value="new"/>
      </td>
    {{/if}}

    <td align="center" {{if $new_profile}}colspan="4"{{/if}} class="text{{if $_value.new == -1}} error
        {{else}}{{if $_value.old != -1 && $_value.old != $_value.new}} warning{{/if}}{{/if}}">
      {{if $_value.new == -1}}
        {{tr}}CUser-import-pref-missing{{/tr}}
      {{else}}
        {{$_value.new}}
      {{/if}}
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td class="empty" align="center" colspan="6">
      {{if $type == 'prefs'}}
        {{tr}}CPreferences.none{{/tr}}
      {{else}}
        {{tr}}No-functional-perm{{/tr}}
      {{/if}}
    </td>
  </tr>
{{/foreach}}