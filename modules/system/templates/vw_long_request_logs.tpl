{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=long_request_log ajax=true}}

<script>
  Main.add(function() {
    Control.Tabs.create('long_request_tabs', true);
  });
</script>

<ul class="control_tabs" id="long_request_tabs">
  <li>
    <a href="#view_logs">
      {{tr}}ClongRequestLog|pl{{/tr}}
    </a>
  </li>

  <li>
    <a href="#view_logs_stats">
      {{tr}}common-Ranking{{/tr}}
    </a>
  </li>
</ul>

<table class="main layout">
  <tr>
    <td id="view_logs" style="display: none;">
      {{mb_include module=system template=view_long_request_logs}}
    </td>

    <td id="view_logs_stats" style="display: none;">
      {{mb_include module=system template=vw_long_request_stats}}
    </td>
  </tr>
</table>