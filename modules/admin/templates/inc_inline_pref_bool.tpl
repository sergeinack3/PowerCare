{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=type value='radio'}}

<label title="{{if $title}}{{tr}}{{$title}}{{/tr}}{{else}}{{tr}}pref-{{$key}}-desc{{/tr}}{{/if}}">
  {{if $type == 'radio'}}
    {{if $show_label}}
      {{if $label}}
        {{tr}}{{$label}}{{/tr}}
      {{else}}
        {{tr}}pref-{{$key}}{{/tr}}
      {{/if}}
    {{/if}}

    <input type="radio" name="{{$name}}" value="1" onchange="App.savePref('{{$key}}', '1');"
      {{if $onclick}}onclick="{{$onclick}}"{{/if}} {{if $app->user_prefs.$key}}checked{{/if}}/>
    {{tr}}common-Yes{{/tr}}

    <input type="radio" name="{{$name}}" value="0" onchange="App.savePref('{{$key}}', '0');"
           {{if $onclick}}onclick="{{$onclick}}"{{/if}} {{if !$app->user_prefs.$key}}checked{{/if}}/>
    {{tr}}common-No{{/tr}}

  {{elseif $type == 'checkbox'}}
    <input type="checkbox" name="{{$name}}" onchange="App.savePref('{{$key}}', (this.checked) ? '1' : '0');"
           {{if $onclick}}onclick="{{$onclick}}"{{/if}} {{if $app->user_prefs.$key}}checked{{/if}}/>

    {{if $show_label}}
      {{if $label}}
        {{tr}}{{$label}}{{/tr}}
      {{else}}
        {{tr}}pref-{{$key}}{{/tr}}
      {{/if}}
    {{/if}}
  {{/if}}
</label>


