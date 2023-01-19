{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{* FA icon classname *}}
{{mb_default var=class value='fa fa-circle'}}

{{* FA icon size *}}
{{mb_default var=font_size value='12px'}}

{{* FA icon color *}}
{{mb_default var=color value='black'}}

{{* FA icon title *}}
{{mb_default var=title value=false}}

{{* FA icon text-shadow *}}
{{mb_default var=border value=false}}
{{if $border}}
  {{mb_default var=border_color value='black'}}
{{/if}}

<i class="{{$class}}" {{if $title}}title="{{tr}}{{$title}}{{/tr}}"{{/if}}
   style="font-size: {{$font_size}}; color: {{$color}};
   {{if $border}}
     text-shadow: -1px -1px 0 {{$border_color}}, 1px -1px 0 {{$border_color}}, -1px 1px 0 {{$border_color}}, 1px 1px 0 {{$border_color}};
   {{/if}}"
></i>