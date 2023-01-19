{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=view_sender}}
{{mb_script module=system script=view_sender_source}}
{{mb_script module=system script=source_to_view_sender}}
{{mb_script module=system script=exchange_source}}

<script>
  Main.add(function() {
    Control.Tabs.create('tabs-main', true).activeLink.onmouseup();
  });
</script>

<ul id="tabs-main" class="control_tabs">
  <li><a href="#senders" onmouseup="ViewSender.refreshList();"      >{{tr}}CViewSender{{/tr}}</a></li>
  <li><a href="#sources" onmouseup="ViewSenderSource.refreshList();">{{tr}}CViewSenderSource{{/tr}}</a></li>
  <li><a href="#dosend"  onmouseup="ViewSender.doSend(0);"           >{{tr}}CViewSender-title-dosend{{/tr}}</a></li>
  <li><a href="#monitor" onmouseup="ViewSender.refreshMonitor();"   >{{tr}}CViewSender-title-monitor{{/tr}}</a></li>
</ul>

<div id="senders" style="display: none;">

  <button class="new singleclick" onclick="ViewSender.edit(0);">
    {{tr}}CViewSender-title-create{{/tr}}
  </button>
  
  <div id="list-senders">
  </div>

</div>

<div id="sources" style="display: none;">
  <button class="new singleclick" onclick="ViewSenderSource.edit(0);">
    {{tr}}CViewSenderSource-title-create{{/tr}}
  </button>
  
  <div id="list-sources">
  </div>

</div>

<div id="dosend" style="display: none;"></div>

<div id="monitor" style="display: none;"></div>
