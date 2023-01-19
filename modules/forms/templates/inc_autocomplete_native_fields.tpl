{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul class="ac-native-fields">
{{foreach from=$host_fields item=element key=value}}
  <li data-prop="{{$element.prop}}"
      data-class="{{$element.class}}"
      data-field="{{$element.field}}"
      data-title="{{$element.title}}"
      data-value="{{$value}}"
      title="{{$element.longview}}">

    <small class="col-class">
      {{if $value|strpos:"CONNECTED_USER" === false}}
        {{tr}}{{$element.class}}{{/tr}}
      {{/if}}
    </small>

    <span class="view" style="{{if !$show_views}} display: none !important; {{/if}}">
      {{if $value|strpos:"CONNECTED_USER" === false}}
        {{tr}}{{$element.class}}{{/tr}} /
      {{/if}}

      {{$element.view}}
    </span>

    <span style="{{if $show_views}} display: none !important; {{/if}} padding-left: {{$element.level}}em; {{if $element.level == 0}} font-weight: bold; {{/if}}">
      {{$element.title}}
    </span>

    <small class="col-type">
      {{$element.type}}
    </small>
  </li>
{{/foreach}}
</ul>
