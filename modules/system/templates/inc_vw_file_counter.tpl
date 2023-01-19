{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=value value=0}}

{{if !$value}}
  {{mb_return}}
{{/if}}

{{* FA icon classname *}}
{{mb_default var=file_icon value='far fa-file'}}

{{mb_default var=size      value=1}}
{{assign     var=icon_size value=''}}

{{if $size > 1}}
  {{assign var=icon_size value="fa-`$size`x"}}
{{/if}}

{{* FA icon color *}}
{{mb_default var=file_color value=''}}
{{mb_default var=color value=''}}

{{* FA icon title *}}
{{mb_default var=title value=false}}

<span class="fa-stack {{$icon_size}}" {{if $title}}title="{{tr}}{{$title}}{{/tr}}"{{/if}}>
  <i class="{{$file_icon}} fa-stack-2x" {{if $file_color}}style="color: {{$file_color}};"{{/if}}></i>
  <strong class="fa-stack-1x fa-stack-text" style="margin-top: .2em; {{if $color}}color: {{$color}};{{/if}}">{{$value}}</strong>
</span>