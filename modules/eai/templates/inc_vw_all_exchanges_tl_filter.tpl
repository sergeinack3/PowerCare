{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=modal value=""}}
{{mb_default var=actor value=""}}

<script type="text/javascript">
  toggleAutoRefresh = function () {
    if (!window.autoRefresh) {
      window.autoRefresh = setInterval(function () {
        getForm("filterExchange").onsubmit();
      }, 5000);
      $("auto-refresh-toggler").style.borderColor = "red";
    }
    else {
      clearTimeout(window.autoRefresh);
      window.autoRefresh = null;
      $("auto-refresh-toggler").style.borderColor = "";
    }
  }

  Main.add(function () {
    getForm('filterExchange').onsubmit();
  });
</script>

<table class="main">
  <tr>
    <th class="title">
      {{tr}}CExchangeDataFormat{{/tr}} du {{$exchange_tl->_date_min|date_format:$conf.datetime}} au
      {{$exchange_tl->_date_max|date_format:$conf.datetime}}
    </th>
  </tr>
  <!-- Filtres -->
  <tr>
    <td style="text-align: center;">
      <form action="?" name="filterExchange" method="get" onsubmit="return ExchangeDataFormat.viewAllTL(this)">
        <input type="hidden" name="m" value="{{$m}}" />

        <table class="main layout">
          <tr>
            <td class="separator expand" onclick="MbObject.toggleColumn(this, $(this).next())"></td>

            <td {{if $modal}}style=" display: none;"{{/if}}>
              <table class="form">
                <tr>
                  <th style="width: 15%">{{mb_label object=$exchange_tl field=date_echange}}</th>
                  <td class="text" style="width: 35%">
                    {{mb_field object=$exchange_tl field=_date_min register=true form="filterExchange"
                    prop=dateTime onchange="\$V(this.form.elements.start, 0)"}}
                    <b>&raquo;</b>
                    {{mb_field object=$exchange_tl field=_date_max register=true form="filterExchange"
                    prop=dateTime onchange="\$V(this.form.elements.start, 0)"}}
                  </td>
                </tr>

                <tr>
                  <td colspan="4">
                    <button type="submit" class="search">{{tr}}Filter{{/tr}}</button>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
      </form>
    </td>
  </tr>
</table>

<hr />

<div id="vw_all_exchanges">

</div>