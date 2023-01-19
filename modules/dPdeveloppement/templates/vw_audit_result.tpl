{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Control.Tabs.create('data-audit-tabs', true);
  });
</script>

<ul id="data-audit-tabs" class="control_tabs">
  <li><a href="#schema-diff-tab">{{tr}}common-Schema{{/tr}}</a></li>
  <li><a href="#log-diff-tab">{{tr}}common-Log|pl{{/tr}}</a></li>
</ul>

<div id="schema-diff-tab" style="display: none;">
  {{mb_include module=developpement template=inc_vw_schema_diff audit=$audit}}
</div>

<div id="log-diff-tab" style="display: none;">
  {{mb_include module=developpement template=inc_vw_log_diff audit=$audit}}
</div>