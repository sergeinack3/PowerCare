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
{{mb_default var=comment_icon value='fa-comment'}}

{{mb_default var=size      value=1}}
{{assign     var=icon_size value=''}}

{{if $size > 1}}
  {{assign var=icon_size value="fa-`$size`x"}}
{{/if}}

{{* FA icon color *}}
{{mb_default var=comment_color value=''}}
{{mb_default var=color value=''}}

{{* FA icon title *}}
{{mb_default var=title value=false}}

{{mb_default var=inverse value=false}}

<span class="fa-stack {{$icon_size}}" {{if $title}}title="{{tr}}{{$title}}{{/tr}}"{{/if}}>
  <i class="far {{$comment_icon}} fa-stack-2x" {{if $comment_color}}style="color: {{$comment_color}};"{{/if}}></i>
  <strong class="fa-stack-1x fa-stack-text {{if $inverse}} fa-inverse {{/if}}" {{if $color}}style="color: {{$color}};"{{/if}}>{{$value}}</strong>
</span>