{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
    {{foreach from=$organisms item=organism}}
        <li data-fund_code="{{$organism.fund_code}}" data-center_code="{{$organism.center_code}}" data-label="{{$organism.label}}">
            <strong>{{$organism.label}}</strong>
            <span class="code"><small>{{$organism.fund_code}} {{$organism.center_code}}</small></span>
        </li>
    {{foreachelse}}
        <li>
            <i>{{tr}}Organism.none{{/tr}}</i>
        </li>
    {{/foreach}}
</ul>
