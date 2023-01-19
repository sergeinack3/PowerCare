{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  ObjectTooltip.modes.accountingCode = {
    module: "stock",
    action: "ajax_show_accounting_code",
    sClass: "tooltip"
  };

  function exportMovements(form) {
    $V(form["export"], 1);
    $V(form["suppressHeaders"], 1);
    form.submit();
    $V(form["export"], 0);
    $V(form["suppressHeaders"], 0);
  }

  Main.add(function () {
    getForm("movement-filter").onsubmit();
  });
</script>

<form name="movement-filter" method="get" action="?export=1" target="_blank" onsubmit="return Url.update(this, 'movements-list')">
  <input type="hidden" name="m" value="stock" />
  <input type="hidden" name="a" value="ajax_list_movements" />
  <input type="hidden" name="export" value="0" />
  <input type="hidden" name="suppressHeaders" value="0" />
  <table class="main form">
    <tr>
      <th>{{mb_label object=$movement field=_datetime_min}}</th>
      <td>{{mb_field object=$movement field=_datetime_min form="movement-filter" register=true}}</td>
      <th>{{mb_label object=$movement field=_datetime_max}}</th>
      <td>{{mb_field object=$movement field=_datetime_max form="movement-filter" register=true}}</td>
      <th>{{mb_label object=$movement field=account}}</th>
      <td>{{mb_field object=$movement field=account prop="str" size=10}}</td>
      <th>{{mb_title object=$movement field=origin_class}}</th>
      <td>{{mb_field object=$movement field=origin_class emptyLabel="All"}}</td>

      <td>
        <button class="search me-primary">{{tr}}Filter{{/tr}}</button>
        <button class="download" onclick="exportMovements(this.form)">{{tr}}Export{{/tr}} CSV</button>
      </td>
    </tr>
  </table>
</form>

<div id="movements-list" class="me-padding-0"></div>