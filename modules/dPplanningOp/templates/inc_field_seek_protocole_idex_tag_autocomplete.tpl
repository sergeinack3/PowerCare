{{*
 * @package Mediboard\locationIntel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{*résultats de recherche de l'autocomplete sur les tags d'identifiants externes*}}

<ul style="text-align: left">
  {{foreach from=$tags item=tag}}
    <li id="autocomplete-{{$tag}}" data-id="{{$tag}}" data-guid="{{$tag}}">
        <span class="view">{{$tag}}</span>
    </li>
    {{foreachelse}}
    <li>
    <span class="informal">
      <span style="font-style: italic;">{{tr}}No result{{/tr}}</span>
    </span>
    </li>
  {{/foreach}}
</ul>