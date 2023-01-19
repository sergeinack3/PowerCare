{{*
* @package Mediboard\System
* @author  SAS OpenXtrem <dev@openxtrem.com>
* @license https://www.gnu.org/licenses/gpl.html GNU General Public License
* @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    new Url("system", "crazy_logs")
      .addParam("mode", "find")
      .addParam("class", "CAccessLog")
      .requestUpdate("crazy_CAccessLog");

    new Url("system", "crazy_logs")
      .addParam("mode", "find")
      .addParam("class", "CAccessLogArchive")
      .requestUpdate("crazy_CAccessLogArchive");

    Control.Tabs.create('tabs-access_log', true);
  });
</script>

<ul id="tabs-access_log" class="control_tabs">
  <li><a href="#access_log">{{tr}}system-part-Log|pl{{/tr}}</a></li>
  <li><a href="#access_log_archive">{{tr}}system-part-Archived log|pl{{/tr}}</a></li>
</ul>

<div id="access_log" style="display: none;">
  <table class="main">
    <tr>
      <th style="width: 50%;">{{tr}}CAccessLog{{/tr}}</th>
    </tr>
    <tr>
      <td>
        <div id="crazy_CAccessLog"></div>
      </td>
    </tr>
  </table>
</div>
