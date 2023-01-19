{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=modal value=""}}
{{mb_default var=actor value=""}}

<script type="text/javascript">
  toggleAutoRefresh = function(){
    if (!window.autoRefresh) {
      window.autoRefresh = setInterval(function(){
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

  Main.add(function() {
    getForm('filterExchange').onsubmit();
  });
</script>

<table class="main">
  <tr>
    <th class="title">
      {{if !$modal}}
      <button onclick="ExchangeDataFormat.toggle();" style="float: left;" class="hslip notext me-tertiary me-dark" type="button" title="{{tr}}CExchangeDataFormat{{/tr}}">
        {{tr}}CExchangeDataFormat{{/tr}}
      </button>
      {{/if}}

      <button onclick="toggleAutoRefresh()" id="auto-refresh-toggler" style="float: right;" class="change notext me-textiary" type="button">
        Auto-refresh (5s)
      </button>

      {{tr}}CExchangeDataFormat{{/tr}} du {{$exchange_df->_date_min|date_format:$conf.datetime}} au {{$exchange_df->_date_max|date_format:$conf.datetime}}
      {{if $actor_guid && $actor}} pour {{$actor->_view}} {{/if}}
    </th>
  </tr>
  <!-- Filtres -->
  <tr>
    <td style="text-align: center;">
      <form action="?" name="filterExchange" method="get" onsubmit="return ExchangeDataFormat.viewAll(this)">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="actor_guid" value="{{$actor_guid}}" />

        <table class="main layout">
          <tr>
            <td class="separator expand" onclick="MbObject.toggleColumn(this, $(this).next())"></td>

            <td {{if $modal}}style=" display: none;"{{/if}}>
              <table class="form">
                <tr>
                  <th style="width: 15%">{{mb_label object=$exchange_df field=send_datetime}}</th>
                  <td class="text me-text-align-left" style="width: 35%">
                    {{mb_field object=$exchange_df field=_date_min register=true form="filterExchange" prop=dateTime onchange="\$V(this.form.elements.start, 0)"}}
                    <b>&raquo;</b>
                    {{mb_field object=$exchange_df field=_date_max register=true form="filterExchange" prop=dateTime onchange="\$V(this.form.elements.start, 0)"}}
                  </td>

                  <th style="width: 15%">{{mb_label object=$exchange_df field="group_id"}}</th>
                  <td style="width: 35%" class="me-text-align-left">
                    {{mb_field object=$exchange_df field="group_id" canNull=true form="filterExchange" autocomplete="true,1,50,true,true"
                    placeholder="Tous les établissements"}}
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