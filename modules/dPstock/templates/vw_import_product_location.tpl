{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=stock script=product_stock}}

<table class="main">
    <tr>
      {{if $errors|@count !== 0}}
        {{mb_include template=import_product_stock_error}}
      {{else}}
        {{mb_include template=import_product_stock_instructions}}
      {{/if}}
    </tr>
  {{if $processed_lines > 0}}
    <div class="small-info">
      <span>{{tr var1=$processed_lines}}CProductStockLocation.processed_imports{{/tr}}</span>
    </div>
  {{/if}}
  {{if $pending_lines && $pending_lines.total > 0}}
    <div class="small-info">
      <span>{{tr var1=$pending_lines.total}}CProductStockLocation.pending_imports{{/tr}}</span>
    </div>
  {{/if}}
  <tr>
    <td>
      <div class="me-text-align-center" id="importProductStockLocation">
        <form name="upload-product-stock-location" enctype="multipart/form-data"
              method="post" onsubmit="return onSubmitFormAjax(this, {useFormAction: true}, 'importProductStockLocation')"
              action="?m=stock&a=ajax_import_product_stock_location">
          <input type="hidden" name="m" value="stock"/>
          <input type="hidden" name="a" value="ajax_import_product_stock_location"/>
          {{mb_include module=system template=inc_inline_upload paste=false extensions=csv multi=false}}
          <button type="button" class="button cancel"
                  onclick="Control.Modal.close();refreshTab('vw_idx_stock_location');">{{tr}}Cancel{{/tr}}</button>
          <button type="submit" class="submit">{{tr}}Import{{/tr}}</button>
        </form>
      </div>
    </td>
  </tr>
</table>
