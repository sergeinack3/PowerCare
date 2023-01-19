{{*
  * @package Mediboard\System
  * @author  SAS OpenXtrem <dev@openxtrem.com>
  * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
  * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
  *}}

{{mb_default var=component value="OxEmpty"}}
{{mb_default var=attributes value=false}}
{{mb_default var=selector value="vue-root"}}

<div class="{{$selector}}"
     vue-component="{{$component}}"
  {{if $attributes}}
    {{foreach from=$attributes item=_attribute_value key=_attribute_name}}
      vue-{{$_attribute_name}}="{{$_attribute_value}}"
    {{/foreach}}
  {{/if}}>
</div>
