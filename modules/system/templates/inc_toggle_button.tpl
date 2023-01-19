{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{unique_id var=unique_name}}

{{mb_default var=toggle_on  value='fa-toggle-on'}}
{{mb_default var=toggle_off value='fa-toggle-off'}}
{{mb_default var=color_on   value='forestgreen'}}
{{mb_default var=color_off  value=''}}

{{mb_default var=use_input      value=true}}
{{mb_default var=input_name     value=$unique_name}}
{{mb_default var=input_disabled value=false}}
{{mb_default var=input_selector value="this.previous('input[type=hidden]')"}}
{{mb_default var=value          value=true}}
{{mb_default var=title          value=false}}
{{mb_default var=inline         value=true}}
{{mb_default var=ignore         value=false}}
{{mb_default var=onchange       value=false}}

{{mb_ternary var=toggle_value test=$value value=$toggle_on other=$toggle_off}}
{{mb_ternary var=color_value  test=$value value=$color_on  other=$color_off}}

{{if $use_input}}
  <input type="hidden" name="{{$input_name}}" value="{{$value}}" {{if $onchange}} onchange="{{$onchange}}"{{/if}}
    {{if $ignore}} data-ignore="1"{{/if}} {{if $input_disabled}} disabled{{/if}} />
{{/if}}

<a href="#1" onclick="toggleButton(this.down('i'), {{$input_selector|JSAttribute}});"
  {{if $inline}} style="display: inline-block;"{{/if}} {{if $title}} title="{{tr}}{{$title}}{{/tr}}"{{/if}}>

  <i class="fa {{$toggle_value}} fa-lg" {{if $color_value}} style="color: {{$color_value}};"{{/if}}
     data-color_on="{{$color_on}}" data-color_off="{{$color_off}}" data-toggle_on="{{$toggle_on}}" data-toggle_off="{{$toggle_off}}"></i>
</a>