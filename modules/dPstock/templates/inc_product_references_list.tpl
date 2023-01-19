{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div id="tab-references" style="display: none;" class="me-padding-0">

  <a class="button new me-margin-8" href="?m=stock&tab=vw_idx_reference&reference_id=0&product_id={{$product->_id}}">
    {{tr}}CProductReference-title-create{{/tr}}
  </a>

  <table class="tbl me-no-align me-no-border-radius-top">
    <tr>
      <th class="narrow">{{mb_title class=CProductReference field=code}}</th>
      <th class="narrow">{{mb_title class=CProductReference field=most_used_ref}}</th>

      <th>{{mb_title class=CProductReference field=societe_id}}</th>

      {{if "dPstock CProductReference show_cond_price"|gconf}}
        <th class="narrow">{{mb_title class=CProductReference field=_cond_price}}</th>
      {{/if}}

      <th colspan="2" class="narrow">{{mb_title class=CProductReference field=price}}</th>
    </tr>

    {{foreach from=$product->_ref_references item=_reference}}
      {{assign var=_product value=$_reference->_ref_product}}
      <tr>
        <td {{if $_reference->cancelled}}class="cancelled"{{/if}}>
          <a href="?m=stock&tab=vw_idx_reference&reference_id={{$_reference->_id}}">
            <strong onmouseover="ObjectTooltip.createEx(this, '{{$_reference->_guid}}')">
              {{if $_reference->code}}
                {{mb_value object=$_reference field=code}}
              {{else}}
                {{tr}}CProductReference.no-code{{/tr}}
              {{/if}}
            </strong>
          </a>
        </td>
        <td class="most_used_ref me-text-align-center">
          {{if $_reference->most_used_ref}}
            <i class="fas fa-star"></i>
          {{/if}}
        </td>
        <td>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$_reference->_ref_societe->_guid}}')">
        {{mb_value object=$_reference field=societe_id}}
      </span>
        </td>

        {{if "dPstock CProductReference show_cond_price"|gconf}}
          <td style="text-align: right;">{{mb_value object=$_reference field=_cond_price}}</td>
        {{/if}}

        <td style="text-align: right;">
          <label title="{{$_reference->quantity}} x {{$_product->item_title}}">
            {{mb_value object=$_reference field=quantity}} x
          </label>
        </td>
        <td style="text-align: right;"><strong>{{mb_value object=$_reference field=price}}</strong></td>
      </tr>
      {{foreachelse}}
      <tr>
        <td colspan="10" class="empty">{{tr}}CProductReference.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  </table>
</div>