{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if "pharmacie"|module_active}}
  {{mb_script module=pharmacie script=pharmacie ajax=$ajax}}
{{/if}}

<script>
  function refreshInventory() {
    var url = new Url("soins", "httpreq_vw_stock_inventory");
    url.addFormData(getForm("filter"));
    url.addFormData(getForm("form_inventory"));
    url.requestUpdate("list-stock_sejour");
  }

  function refreshInventorySejour(sejour_id) {
    var url = new Url("soins", "httpreq_vw_stock_inventory_sejour");
    if (sejour_id) {
      $('inventaire_'+sejour_id).addUniqueClassName("selected");
      url.addParam('sejour_id', sejour_id);
    }
    url.requestUpdate("stock_inventory_sejour");
  }

  Main.add(function () {
    refreshInventory();
  });
</script>

<form name="form_inventory" action="?" method="get" onsubmit="return refreshInventory();">
  <table class="main form me-no-align">
    <tr>
      <th>
        {{mb_label object=$filter field=_filter_date_min}}
      </th>
      <td>
        {{mb_field object=$filter field=_filter_date_min form="form_inventory" register=true}}
      </td>
      <th>
        {{mb_label object=$filter field=_filter_date_max}}
      </th>
      <td>
        {{mb_field object=$filter field=_filter_date_max form="form_inventory" register=true}}
      </td>
    </tr>
    <tr>
      <td colspan="4" class="button">
        <button type="button" class="search me-primary" onclick="refreshInventory();">{{tr}}Filter{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="list-stock_sejour"></div>