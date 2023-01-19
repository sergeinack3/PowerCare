{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
  {{foreach from=$matches item=mutuelle}}
    <li>
      <p class="name" data-name="{{$mutuelle->name}}">
        {{$mutuelle->name}}
      </p>
      -
      <small class="code" data-code="{{$mutuelle->code}}">
        {{$mutuelle->code}}
      </small>
    </li>
    {{foreachelse}}
    <li>
      {{tr}}CHealthInsurance-no matches{{/tr}}
    </li>
  {{/foreach}}
</ul>
