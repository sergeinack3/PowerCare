{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main">
  {{if $extractPassages->message_xml}}
    <tr>
      <th class="title">{{mb_title object=$extractPassages field="message_xml"}}</th>
    </tr>
    <tr>
      <td>
        <div style="height: 400px;" class="highlight-fill">
          {{mb_value object=$extractPassages field="message_xml" advanced=true}}
        </div>
      </td>
    </tr>
  {{else}}
    <tr>
      <th class="title">{{mb_title object=$extractPassages field="message_any"}}</th>
    </tr>
    <tr>
      <td>
        <div style="height: 400px;" class="highlight-fill">
          {{mb_value object=$extractPassages field="message_any" advanced=true}}
        </div>
      </td>
    </tr>
  {{/if}}
</table>