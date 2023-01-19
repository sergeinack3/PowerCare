{{*
 * @package Mediboard\dpCabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
  {{foreach from=$codes item=_code}}
    <li data-code="{{$_code->code}}">
      <strong class="code">{{$_code->code}}</strong>
      <strong><small>{{$_code->libelle}}</small></strong>
    </li>
    {{foreachelse}}
    <li>
      <span style="font-style: italic;">{{tr}}No result{{/tr}}</span>
    </li>
  {{/foreach}}
</ul>