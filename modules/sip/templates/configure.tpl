{{*
 * @package Mediboard\Sip
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(Control.Tabs.create.curry('tabs-configure', true));
</script>

<ul id="tabs-configure" class="control_tabs">
  <li><a href="#SIP">{{tr}}SIP{{/tr}}</a></li>
  <li><a href="#actions">{{tr}}sip_config-actions{{/tr}}</a></li>
</ul>

<div id="SIP" style="display: none;">
  {{mb_include template=inc_config_sip}}
</div>

<div id="actions" style="display: none;">
  {{mb_include template=inc_config_actions}}
</div>