{{*
 * @package Mediboard\Ftp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(Control.Tabs.create.curry('tabs-configure', true));
</script>

<ul id="tabs-configure" class="control_tabs">
  <li><a href="#config-read-files-senders">{{tr}}config-read-files-senders{{/tr}}</a></li>
  <li><a href="#config-purge_echange">{{tr}}config-ftp-purge-echange{{/tr}}</a></li>
</ul>

<div id="config-read-files-senders" style="display: none;">
  {{mb_include template=inc_config_read_files_senders}}
</div>

<div id="config-purge_echange" style="display: none;">
  {{mb_include template=inc_config_purge_echange}}
</div>