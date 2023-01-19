{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl" id="history-legend" style="display: none; max-width: 600px;">
  <col style="width: 50px;" />

  <tr>
    <th colspan="2" class="title">{{tr}}Legend{{/tr}}</th>
  </tr>

  <tr>
    <th class="category" colspan="2"> {{tr}}CInteropActor-role{{/tr}} </th>
  </tr>

  <tr>
    <td class="narrow">
      <strong style="color: red">{{tr}}CInteropActor-role.prod-court{{/tr}}</strong>
    </td>
    <td>
      {{tr}}CInteropActor-role.prod-desc{{/tr}}
    </td>
  </tr>

  <tr>
    <td class="narrow">
      <span style="color: green">{{tr}}CInteropActor-role.qualif-court{{/tr}}</span>
    </td>
    <td>
      {{tr}}CInteropActor-role.qualif-desc{{/tr}}
    </td>
  </tr>

  <tr>
    <td class="narrow">
      <i class="fa fas fa-exclamation-triangle" style="color: goldenrod;"></i>
    </td>
    <td>
      {{tr}}CInteropActor-msg-Actor role incompatible with instance role{{/tr}}
    </td>
  </tr>

  <tr>
    <th class="category" colspan="2"> {{tr}}CInteropActor-actif{{/tr}} </th>
  </tr>

  <tr>
    <td class="narrow">
      <i class="fa fa-toggle-on" style="color: #449944; font-size: large;"></i>
    </td>
    <td>
      {{tr}}CInteropActor-actif.1{{/tr}}
    </td>
  </tr>

  <tr>
    <td class="narrow">
      <i class="fa fa-toggle-off" style="font-size: large;"></i>
    </td>
    <td>
      {{tr}}CInteropActor-actif.0{{/tr}}
    </td>
  </tr>

  <tr>
    <th class="category" colspan="2"> {{tr}}CInteropActor-Synchronization{{/tr}} </th>
  </tr>

  <tr>
    <td class="narrow">
      <i class="fas fa-exchange-alt" style="font-size: large;"></i>
    </td>
    <td>
      {{tr}}CInteropReceiver-msg-bidirectional synchronization{{/tr}}
    </td>
  </tr>

  <tr>
    <td class="narrow">
      <i class="fas fa-long-arrow-alt-right" style="font-size: large;"></i>
    </td>
    <td>
      {{tr}}CInteropReceiver-msg-unidirectional synchronization{{/tr}}
    </td>
  </tr>

  <tr>
    <th class="category" colspan="2"> {{tr}}CInteropActor-_ref_exchanges_sources{{/tr}} </th>
  </tr>

  <tr>
    <td class="narrow">
      <i class="fa fa fa-circle" style="color: grey;"></i>
    </td>
    <td>
      {{tr}}CExchangeSource._reachable.none-desc{{/tr}}
    </td>
  </tr>

  <tr>
    <td class="narrow">
      <i class="fa fa fa-circle" style="color: red;"></i>
    </td>
    <td>
      {{tr}}CExchangeSource._reachable.0-desc{{/tr}}
    </td>
  </tr>

  <tr>
    <td class="narrow">
      <i class="fa fa fa-circle" style="color: orange;"></i>
    </td>
    <td>
      {{tr}}CExchangeSource._reachable.1-desc{{/tr}}
    </td>
  </tr>

  <tr>
    <td class="narrow">
      <i class="fa fa fa-circle" style="color: limegreen;"></i>
    </td>
    <td>
      {{tr}}CExchangeSource._reachable.2-desc{{/tr}}
    </td>
  </tr>

  <tr>
    <th class="category" colspan="2"> {{tr}}CInteropActor-_last_exchange_time{{/tr}} </th>
  </tr>

  <tr>
    <td class="narrow">
      <i class="fa fa-ban" style="color: black;"></i>
    </td>
    <td>
      {{tr}}CInteropActor-msg-No message sent{{/tr}}
    </td>
  </tr>

  <td class="narrow">
    <i class="fa fa-hourglass" style="color: red;"></i>
  </td>
  <td>
    {{tr}}CInteropActor-msg-Delayed messages %s at %s{{/tr}}
  </td>
</table>