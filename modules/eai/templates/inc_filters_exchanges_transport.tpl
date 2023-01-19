{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=mod_name value=$exchange->_ref_module->mod_name}}

<script>
  Main.add(function () {
    getForm('filterExchange').onsubmit();
  });
</script>

<table class="main">
  <tr>
    <th class="title">
      {{tr}}{{$exchange->_class}}{{/tr}} du {{$exchange->_date_min|date_format:$conf.datetime}}
      au {{$exchange->_date_max|date_format:$conf.datetime}}
    </th>
  </tr>
  <!-- Filtres -->
  <tr>
    <td style="text-align: center">
      <form action="?" name="filterExchange" method="get" onsubmit="return ExchangeDataFormat.refreshExchangesListTransport(this)">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="page" value="{{$page}}" onchange="this.form.onsubmit()" />
        <input type="hidden" name="exchange_class" value="{{$exchange->_class}}" />
        <input type="hidden" name="order_col" value="date_echange" />
        <input type="hidden" name="order_way" value="DESC" />

        <table class="main layout">
          <tr>
            <td class="separator expand" onclick="MbObject.toggleColumn(this, $(this).next())"></td>

            <td>
              <table class="main form">
                <tr>
                  <th style="width: 15%">{{mb_label object=$exchange field=date_echange}}</th>
                  <td class="text" style="width: 35%">
                    {{mb_field object=$exchange field=_date_min register=true form="filterExchange"
                    prop=dateTime onchange="\$V(this.form.elements.start, 0)"}}
                    <b>&raquo;</b>
                    {{mb_field object=$exchange field=_date_max register=true form="filterExchange"
                    prop=dateTime onchange="\$V(this.form.elements.start, 0)"}}
                  </td>

                  <th></th>
                  <td></td>
                </tr>

                <tr>
                  <th>{{mb_label object=$exchange field=input}}</th>
                  <td>
                    <input type="text" name="keywords_input" value="{{$keywords_input}}"
                           placeholder="{{tr}}CExchangeTransportLayer-legend-Keywords input{{/tr}}" size="60px" />
                  </td>

                  <th>{{mb_label object=$exchange field=output}}</th>
                  <td>
                    <input type="text" name="keywords_output" value="{{$keywords_output}}"
                           placeholder="{{tr}}CExchangeTransportLayer-legend-Keywords output{{/tr}}" size="60px" />
                  </td>
                </tr>

                <tr>
                  <th>{{tr}}Filter{{/tr}}</th>
                  <td colspan="3">
                    {{foreach from=$filter_types key=status_type item=_type}}
                      <fieldset style="display: inline-block;
                        background-color: {{if $status_type == "error"}}rgba(255, 102, 102, 0.4){{else}}rgba(148, 221, 137, 0.4){{/if}} !important; margin-top: 0;">
                        {{foreach from=$_type key=type item=value}}
                          <label>
                            <input onclick="$V(this.form.page, 0)" type="checkbox" name="types[{{$type}}]" />
                            {{tr}}CExchange-type-{{$type}}{{/tr}}
                          </label>
                        {{/foreach}}
                      </fieldset>
                    {{/foreach}}
                  </td>
                </tr>

                {{mb_include module=$mod_name template="`$exchange->_class`_filter_inc" ignore_errors=true}}

                <tr>
                  <td colspan="4">
                    <button type="submit" class="search">{{tr}}Filter{{/tr}}</button>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </form>
    </td>
  </tr>

  <tr>
    <td class="halfPane" rowspan="3" id="exchangesTransportList">
    </td>
  </tr>
</table>