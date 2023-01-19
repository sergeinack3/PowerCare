{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$object->_can->read}}
  <div class="small-info">
    {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

{{unique_id var=consumption_id}}

<script>
  Main.add(function () {
    var url = new Url("stock", "httpreq_vw_product_consumption_graph");
    url.addParam("product_id", {{$object->_id}});
    url.requestUpdate("product-consumption-{{$consumption_id}}");
  });
</script>

{{mb_include module=system template=CMbObject_view}}

<div id="product-consumption-{{$consumption_id}}"></div>

{{if $object->_can->edit}}
  <table class="main tbl">
    <tr>
      <td class="button">
        <a class="button edit" href="?m=stock&tab=vw_idx_product&product_id={{$object->_id}}">
          {{tr}}Edit{{/tr}}
        </a>
      </td>
    </tr>
  </table>
{{/if}}
