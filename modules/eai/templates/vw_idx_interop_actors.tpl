{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=eai script=interop_actor}}
{{mb_script module=eai script=exchange_data_format}}
{{mb_script module="system" script="object_selector"}}

<script>
  Main.add(function() {
    tabs = Control.Tabs.create('tabs-actors', false, {
      afterChange: function(newContainer) {
        switch (newContainer.id) {
          case "CInteropReceivers" :
            InteropActor.refreshActors('CInteropReceiver');
            break;
          case "CInteropSenders" :
            InteropActor.refreshActors('CInteropSender');
            break;
        }
      }
    });

    var interop_actor_guid = Url.hashParams().interop_actor_guid;

    if (interop_actor_guid) {
      InteropActor.viewActor(interop_actor_guid);
    }
  });
</script>

<table class="main">
  <tr>
    <td style="width: 40%">
      <ul id="tabs-actors" class="control_tabs small">
        <li>
          <a href="#CInteropReceivers">
            {{tr}}CInteropReceiver-court{{/tr}}
            (&ndash; / &ndash;)
          </a>
        </li>
        <li>
          <a href="#CInteropSenders">
            {{tr}}CInteropSender-court{{/tr}}
            (&ndash; / &ndash;)
          </a>
        </li>
      </ul>

      <div id="CInteropReceivers" style="display: none;">
        {{mb_include template=inc_actors actor=$receiver actors=$receivers parent_class="CInteropReceiver"}}
      </div>

      <div id="CInteropSenders" style="display: none;">
        {{mb_include template=inc_actors actor=$sender actors=$senders parent_class="CInteropSender"}}
      </div>
    </td>
    <td style="width: 65%" class="halfPane" id="actor">
    </td> 
  </tr>
</table>
