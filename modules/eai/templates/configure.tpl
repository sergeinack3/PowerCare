{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<script>
    Main.add(function () {
      var senders = JSON.parse('{{$actor_classes.sender|@json_encode}}');
      var receivers = JSON.parse(('{{$actor_classes.receiver|@json_encode}}'));
        Control.Tabs.create('tabs-configure', true);
        Control.Tabs.create('tabs-configure-actors', true, {
          afterChange: function (container) {
            if (container.id === "config-actor-sender") {
              Configuration.edit('eai', senders, $('config-actor-sender'));
            } else if (container.id === "config-actor-receiver") {
              Configuration.edit('eai', receivers, $('config-actor-receiver'));
            }
          }
        });
    });

    function importAsipTable() {
        new Url("eai", "ajax_import_asip_table")
            .requestUpdate("import-log");
    }

    function seeAsipDB() {
        new Url('eai', 'ajax_view_asip_db')
            .requestModal();
    }

    function updateASIPDB() {
      new Url('eai', 'updateASIPDb')
        .requestUpdate('report-update')
    }
</script>

<ul id="tabs-configure" class="control_tabs">
    <li><a href="#object-servers">{{tr}}config-object-servers{{/tr}}</a></li>
    <li><a href="#config-eai">{{tr}}config-eai{{/tr}}</a></li>
    <li><a href="#config-import-asip">{{tr}}ASIP{{/tr}}</a></li>
    <li><a href="#config-tunnel">{{tr}}Tunnel{{/tr}}</a></li>
    <li><a href="#maintenance">{{tr}}Maintenance{{/tr}}</a></li>
    <li><a href="#config-actors">{{tr}}config-actors{{/tr}}</a></li>
</ul>

<div id="object-servers" style="display: none;">
    {{mb_include template=inc_config_object_servers}}
</div>

<div id="config-eai" style="display: none;">
    {{mb_include template=inc_config_eai}}
</div>

<div id="config-import-asip" style="display: none;">
    {{mb_include template=inc_config_import_asip}}
</div>

<div id="config-tunnel" style="display: none;">
    {{mb_include template=inc_config_tunnel}}
</div>

<div id="maintenance" style="display: none;">
    {{mb_include template=inc_config_maintenance}}
</div>

<div id="config-actors" style="display: none">
  <ul id="tabs-configure-actors" class="control_tabs">
    <li><a href="#config-actor-sender">{{tr}}CInteropSender{{/tr}}</a></li>
    <li><a href="#config-actor-receiver">{{tr}}CInteropReceiver{{/tr}}</a></li>
  </ul>

  <div id="config-actor-sender" style="display: none"></div>
  <div id="config-actor-receiver" style="display: none"></div>
</div>
