{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  function changePage(page) {
    $V(getForm("filter-receptions").start, page);
  }

  Main.add(function () {
    getForm("filter-receptions").onsubmit();
  });
</script>

{{mb_script module=stock script=order_manager}}

<form name="filter-receptions" method="get" action="" onsubmit="return Url.update(this, 'receptions_list')">
  <input type="hidden" name="m" value="dPstock" />
  <input type="hidden" name="a" value="httpreq_vw_receptions_list" />
  <input type="hidden" name="start" value="{{$start}}" onchange="this.form.onsubmit()" />
</form>

<div id="receptions_list"></div>
