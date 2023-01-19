{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=circle value=0}}

{{* FA icon classname *}}
{{mb_ternary var=ok_style test=$circle value='far fa-check-circle' other='fa fa-check'}}
{{mb_ternary var=ko_style test=$circle value='far fa-times-circle' other='fas fa-times'}}
{{mb_default var=ok value=$ok_style}}
{{mb_default var=ko value=$ko_style}}

{{mb_default var=size      value=1}}
{{assign     var=icon_size value=''}}

{{if $size == 'lg'}}
  {{assign var=icon_size value='fa-lg'}}
{{elseif $size > 1}}
  {{assign var=icon_size value="fa-`$size`x"}}
{{/if}}

{{* FA icon color *}}
{{mb_default var=ok_color value='forestgreen'}}
{{mb_default var=ko_color value='firebrick'}}

{{* FA icon title *}}
{{mb_default var=ok_title value='common-Yes'}}
{{mb_default var=ko_title value='common-No'}}

{{mb_ternary var=test  test=$value value=$ok other=$ko}}
{{mb_ternary var=color test=$value value=$ok_color other=$ko_color}}
{{mb_ternary var=title test=$value value=$ok_title other=$ko_title}}

<i class="{{$test}} {{$icon_size}}" style="color: {{$color}};" title="{{tr}}{{$title}}{{/tr}}"></i>