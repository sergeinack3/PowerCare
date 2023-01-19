{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<select name="pref[{{$var}}]">
  {{if $user_id !== "default"}}
    <option value="">&mdash; {{tr}}Ditto{{/tr}}</option>
  {{/if}}

  <option value="0"{{if $pref.user === "0"}}selected{{/if}}>{{tr}}bool.0{{/tr}}</option>
  <option value="1"{{if $pref.user === "1"}}selected{{/if}}>{{tr}}bool.1{{/tr}}</option>
</select>
