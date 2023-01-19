{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=eai script=exchange_data_format}}

<script>
  Main.add(function () {
    tabs = Control.Tabs.create('tabs-exchanges', false, {
      afterChange: function (newContainer) {
        switch (newContainer.id) {
          case "CExchangeDataFormats" :
            ExchangeDataFormat.refreshExchangeList('CExchangeDataFormat');
            break;
          case "CExchangeTransportLayers" :
            ExchangeDataFormat.refreshExchangeList('CExchangeTransportLayer');
            break;
        }
      }
    });
  });
</script>

<table class="main">
  <tr>
    <td style="width: 15%" id="exchange_data_format">
      <ul id="tabs-exchanges" class="control_tabs small">
        <li>
          <a href="#CExchangeDataFormats">
            {{tr}}CExchangeDataFormat-court{{/tr}}
          </a>
        </li>
        <li>
          <a href="#CExchangeTransportLayers">
            {{tr}}CExchangeTransportLayer-court{{/tr}}
          </a>
        </li>
      </ul>

      <div id="CExchangeDataFormats" style="display: none;" class="me-padding-0">
      </div>

      <div id="CExchangeTransportLayers" style="display: none;" class="me-padding-0">
      </div>
    </td>
    <td style="width: 85%" class="halfPane" id="exchanges">
    </td>
  </tr>
</table>