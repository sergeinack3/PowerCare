{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
    {{foreach from=$list_products item=_product}}
      <li style="text-align: left;">
        <small style="display: none;" class="ucd-view">{{$_product->libelle}}</small>
          {{tr}}common-Product{{/tr}}:
        <strong class="libelle">
            {{$_product->libelle|emphasize:$keyword}}
        </strong>
        <br>
        <small class="forme">
          <span class="opacity-50">

          </span>
        </small>
      </li>
        {{foreachelse}}
      <li style="text-align: left;"><span class="informal empty">{{tr}}No result{{/tr}}</span></li>
    {{/foreach}}
</ul>
