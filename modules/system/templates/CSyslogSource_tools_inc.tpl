{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=syslog ajax=true}}

<tr>
  <td>
    <fieldset>
      <legend>{{tr}}CSyslogSource-Utilities{{/tr}}</legend>

      {{if $_source->protocol == 'UDP'}}
        <div class="small-info">
          {{tr}}CSyslogSource-msg-Because of UDP is unreliable, tests will not be effective.{{/tr}}
        </div>
      {{/if}}

      <table class="main tbl">
        <tr>
          <td class="narrow">
            <button type="button" class="lookup compact" onclick="SYSLOG.test('{{$_source->name}}', 'connection')">
              {{tr}}common-action-Test connection{{/tr}}
            </button>
          </td>

          <td id="syslog_test" class="greedyPane" rowspan="2"></td>
        </tr>

        <tr>
          <td class="narrow">
            <button type="button" class="send compact" onclick="SYSLOG.test('{{$_source->name}}', 'send');">
              {{tr}}common-action-Test sending{{/tr}}
            </button>
          </td>
        </tr>
      </table>
    </fieldset>
  </td>
</tr>
