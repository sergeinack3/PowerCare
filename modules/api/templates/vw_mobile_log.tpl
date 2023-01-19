{{*
 * @package Mediboard\AppFine
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="api" script="api"}}

<script>
  changePage = function (page) {
    $V(getForm('search_mobile_log').page, page);
  }

  Main.add(function () {
    getForm('search_mobile_log').onsubmit();
  });
</script>

<form name="search_mobile_log" method="get" onsubmit="return api.searchMobileLogMultiCriteria(this);">
  <input type="hidden" name="m" value="api" />
  <input type="hidden" name="page" value="{{$page}}" onchange="this.form.onsubmit()" />
  <table class="main layout">
    <tr>
      <td class="separator expand" onclick="MbObject.toggleColumn(this, $(this).next())"></td>
      <td>
        <table class="main form">
          <tr>
            <th class="narrow">{{tr}}CMobileLog-log_datetime{{/tr}}</th>
            <td onchange="this.form.page = 0" colspan="3">
              {{mb_field object=$mobile_log field=_date_min register=true form="search_mobile_log"
                prop=dateTime style="width:120px !important;"}}
              <strong>&raquo;</strong>
              {{mb_field object=$mobile_log field=_date_max register=true form="search_mobile_log"
                prop=dateTime style="width:120px !important;"}}
            </td>
          </tr>
          <tr>
            <td colspan="4">
              <button type="submit" class="button search" title="{{tr}}Filter{{/tr}}">{{tr}}Filter{{/tr}}</button>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</form>

<div id="result_search_mobile_log" class="me-padding-0"></div>