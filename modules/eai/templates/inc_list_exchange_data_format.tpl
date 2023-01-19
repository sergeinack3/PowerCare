{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    ExchangeDataFormat.viewAllFilter();
  });
</script>

<table class="tbl me-no-box-shadow me-no-align">
  <tr>
    <th>
      <button class="button lookup notext" onclick="ExchangeDataFormat.viewAllFilter();">
        {{tr}}CExchangeDataFormat-view_all{{/tr}}
      </button>
    </th>
    <th class="me-text-align-center"><i class="fas fa-exchange-alt"></i></th>
    <th class="me-text-align-center"><i class="fa fa-database" title="{{tr}}Data{{/tr}} + {{tr}}Indexes{{/tr}} (MB)"></i></th>
    <th class="me-text-align-center"><i class="fa fa-database" title="{{tr}}Free{{/tr}} (MB)"></i></th>
  </tr>
  {{foreach from=$exchanges_data_format_classes key=sub_classes item=_child_classes}}
    <tr>
      <th class="section" colspan="6">
        {{tr}}{{$sub_classes}}{{/tr}}
      </th>
    </tr>
    {{foreach from=$_child_classes item=_exchange_class}}
      {{assign var=sizes value=$_exchange_class->_mysql_infos}}
    <tr>
      <td class="narrow" style="text-align: center">
        <a href="#" onclick="ExchangeDataFormat.refreshExchanges('{{$_exchange_class->_class}}', null, '{{$g}}');"
           title="{{tr}}View{{/tr}} {{tr}}{{$_exchange_class->_class}}{{/tr}}">
            {{tr}}{{$_exchange_class->_class}}-court{{/tr}}
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
  {{/foreach}}
</table>
