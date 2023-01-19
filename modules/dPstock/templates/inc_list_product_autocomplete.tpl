{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
    {{foreach from=$list_products item=_product}}
      {{assign var=product_id value=$_product->_id}}
      <li style="text-align: left;">
        <small style="display: none;" class="code">{{$_product->code}}</small>
        <small style="display: none;" class="ucd-view">{{$_product->name}}</small>
        <small style="display: none;" class="quantity">{{$quantities.$product_id}}</small>

        {{tr}}common-Product{{/tr}}:
        <strong class="libelle">
          {{$_product->name|emphasize:$keywords}}
        </strong>
        <br>
        <small class="forme">
          <span class="opacity-50">
            {{$_product->item_title}}
          </span>
        </small>
      </li>
    {{foreachelse}}
      <li style="text-align: left;"><span class="informal empty">{{tr}}No result{{/tr}}</span></li>
    {{/foreach}}
</ul>
