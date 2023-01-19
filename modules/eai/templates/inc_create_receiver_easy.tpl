{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    tabs = Control.Tabs.create('create-receiver-easy', true,  {
      afterChange: function(newContainer){
        switch (newContainer.id) {
          case "exchange" :
            InteropActor.refreshSummaryReceiver('{{$actor->_guid}}');
          case "source" :
          {{if $actor->_id}}
            InteropActor.refreshSourceReceiver('{{$actor->_guid}}');
          {{/if}}
            break;
          case "configs_receiver" :
          {{if $actor->_id}}
            InteropActor.refreshConfigurationReceiver('{{$actor->_guid}}');
          {{/if}}
            break;
        }
      }
    });

    {{if $tabs_menu}}
      tabs.setActiveTab("{{$tabs_menu}}");
    {{/if}}
  });
</script>

<ul id="create-receiver-easy" class="control_tabs">
  <li><a href="#receiver_type" id="menu_receiver_type">{{tr}}CInteropReceiver{{/tr}}</a></li>
  <li><a href="#exchange" id="menu_exchange" class="empty">{{tr}}CExchange{{/tr}}</a></li>
  <li><a href="#source" id="menu_source" class="empty">{{tr}}CEchangeSOAP-source_id{{/tr}}</a></li>
  <li>
    <a href="#configs_receiver" id="menu_configs_receiver" class="empty">{{tr}}CGroups-back-object_configs{{/tr}}</a>
  </li>
</ul>

<div id="receiver_type" style="display: none">
  {{mb_include module=eai template=inc_type_receiver}}
</div>

<div id="exchange" style="display: none">
  <div id="exchanges">
    {{mb_include module=eai template=inc_choose_exchange}}
  </div>
</div>

<div id="source" style="display: none">
  {{mb_include module=eai template=inc_choose_sources}}
</div>

<div id="configs_receiver" style="display: none">
  {{mb_include module=eai template=inc_choose_config}}
</div>