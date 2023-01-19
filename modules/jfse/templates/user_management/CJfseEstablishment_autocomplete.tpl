{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
  {{foreach from=$establishments item=establishment}}
    <li data-id="{{$establishment->id}}">
      <strong class="view">{{$establishment->name}}</strong>
      <small>{{$establishment->category}}</small>
    </li>
    {{foreachelse}}
    <li>
      <span style="font-style: italic;">{{tr}}No result{{/tr}}</span>
    </li>
  {{/foreach}}
</ul>
