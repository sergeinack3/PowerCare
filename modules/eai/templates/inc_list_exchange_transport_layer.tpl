{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    ExchangeDataFormat.viewAllTLFilter();
  });
</script>

<table class="tbl me-no-box-shadow me-no-align">
  <tr>
    <th>
      <button class="button lookup notext" onclick="ExchangeDataFormat.viewAllTLFilter();">
        {{tr}}CExchangeTransportLayer-view_all{{/tr}}
      </button>
    </th>
    <th class="me-text-align-center"><i class="fas fa-exchange-alt"></i></th>
    <th class="me-text-align-center"><i class="fa fa-database" title="{{tr}}Data{{/tr}} + {{tr}}Indexes{{/tr}} (MB)"></i></th>
    <th class="me-text-align-center"><i class="fa fa-database" title="{{tr}}Loss{{/tr}} (MB)"></i></th>
  </tr>
  {{foreach from=$exchanges_transport_layer_classes key=_exchange_class_name item=_exchange_class}}
    {{assign var=sizes value=$_exchange_class->_mysql_infos}}
    <tr>
      <td class="narrow" style="text-align: center">
        <a href="#" onclick="ExchangeDataFormat.refreshExchangesTransport('{{$_exchange_class_name}}', null);"
           title="{{tr}}View{{/tr}} {{tr}}{{$_exchange_class_name}}{{/tr}}">
            {{tr}}{{$_exchange_class_name}}-court{{/tr}}
        </a>
      </td>
      <td style="text-align: right; {{if $_exchange_class->_count_exchanges == "0"}}color: #ccc{{/if}}">
        {{$_exchange_class->_count_exchanges|decasi:""}}</td>
      <td style="text-align: right; {{if $sizes.size == "0.00"}}color: #ccc{{/if}}">
        {{$sizes.size|decabinary}}</td>
      <td style="text-align: right; {{if $sizes.data_free == "0.00"}}color: #ccc{{/if}}">
        {{$sizes.data_free|decabinary}}</td>
    </tr>
  {{/foreach}}
</table>
