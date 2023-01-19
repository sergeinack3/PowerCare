{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=values     value='|'|explode:$value}}
{{assign var=components value='|'|explode:$_prop.components}}
{{assign var=features   value=' '|explode:$_feature}}
{{assign var=_class     value=$features.1}}
{{assign var=field      value=$features|@last}}

{{assign var=display value=$values.0}}
{{assign var=visual  value=$values.1}}
{{assign var=audio   value=$values.2}}
{{assign var=notif   value=$values.3}}

{{assign var=visual_threshold value=$values.4}}
{{assign var=audio_threshold  value=$values.5}}
{{assign var=notif_threshold  value=$values.6}}

{{assign var=extra   value='|'|explode:$_prop.extra}}
{{assign var=type    value=$extra|@first}}
{{assign var=options value=$extra|@array_slice:1}}

{{if $is_last}}
  <script>
    updateConfigValues = function(feature) {
      var form = getForm('edit-configuration-{{$uid}}');

      var value = $V(form[feature + '-display'])
        + '|' + $V(form[feature + '-visual'])
        + '|' + $V(form[feature + '-audio'])
        + '|' + $V(form[feature + '-notif'])
        + '|' + $V(form[feature + '-visual_threshold'])
        + '|' + $V(form[feature + '-audio_threshold'])
        + '|' + $V(form[feature + '-notif_threshold']);

      var input = $A(form.elements['c[' + feature + ']']).filter(function(element) {
        return !element.hasClassName('inherit-value');
      });

      $V(input[0], value);
    }
  </script>
{{/if}}

{{if $is_last}}
  <input type="hidden" name="c[{{$_feature}}]" value="{{'|'|implode:$values}}" {{if $is_inherited}}disabled{{/if}} />

  <table class="layout" style="display: inline-table; width: 80%;">
    <tr>
      <td style="width: 25%; text-align: center;">
        <i class="far fa-chart-bar fa-lg" title="{{tr}}common-action-Display{{/tr}}"></i>
        {{mb_include module=system template=inc_toggle_button input_name="$_feature-`$components.0`" value=$display input_disabled=$is_inherited ignore=true onchange="updateConfigValues('`$_feature`');" title='common-action-Display'}}
      </td>

      <td style="width: 25%; text-align: center;">
        <i class="fa fa-eye fa-lg" title="{{tr}}CMonitorAlert-Visual alert{{/tr}}"></i>
        {{mb_include module=system template=inc_toggle_button input_name="$_feature-`$components.1`" value=$visual input_disabled=$is_inherited ignore=true onchange="updateConfigValues('`$_feature`');"}}

        {{if $type == 'classic'}}
          <input type="text" name="{{$_feature}}-{{$components.4}}" value="{{$visual_threshold}}" {{if $is_inherited}}disabled{{/if}}
                 size="3" data-ignore="1" onchange="updateConfigValues('{{$_feature}}');" />
        {{elseif $type == 'status' || $type == 'date'}}
          <select name="{{$_feature}}-{{$components.4}}" data-ignore="1" onchange="updateConfigValues('{{$_feature}}');"
                  {{if $is_inherited}}disabled{{/if}}>
            {{foreach from=$options item=_option}}
              <option value="{{$_option}}" {{if $visual_threshold == $_option}}selected{{/if}}>
                {{tr}}{{$_class}}.{{$field}}.{{$_option}}{{/tr}}
              </option>
            {{/foreach}}
          </select>
        {{elseif $type == 'specific'}}
          {{* Nothing *}}
        {{/if}}
      </td>

      <td style="width: 25%; text-align: center;">
        <i class="fa fa-volume-up fa-lg" title="{{tr}}CMonitorAlert-Audio alert{{/tr}}"></i>
        {{mb_include module=system template=inc_toggle_button input_name="$_feature-`$components.2`" value=$audio input_disabled=$is_inherited ignore=true onchange="updateConfigValues('`$_feature`');"}}

        {{if $type == 'classic'}}
          <input type="text" name="{{$_feature}}-{{$components.5}}" value="{{$audio_threshold}}" {{if $is_inherited}}disabled{{/if}}
                 size="3" data-ignore="1" onchange="updateConfigValues('{{$_feature}}');" />
        {{elseif $type == 'status' || $type == 'date'}}
          <select name="{{$_feature}}-{{$components.5}}" data-ignore="1" onchange="updateConfigValues('{{$_feature}}');"
                  {{if $is_inherited}}disabled{{/if}}>
            {{foreach from=$options item=_option}}
              <option value="{{$_option}}" {{if $audio_threshold == $_option}}selected{{/if}}>
                {{tr}}{{$_class}}.{{$field}}.{{$_option}}{{/tr}}
              </option>
            {{/foreach}}
          </select>
        {{elseif $type == 'specific'}}
          {{* Nothing *}}
        {{/if}}
      </td>

      <td style="width: 25%; text-align: center;">
        <i class="fa fa-envelope" title="{{tr}}common-Notification{{/tr}}"></i>
        {{mb_include module=system template=inc_toggle_button input_name="$_feature-`$components.3`" value=$notif input_disabled=$is_inherited ignore=true onchange="updateConfigValues('`$_feature`');"}}

        {{if $type == 'classic'}}
          <input type="text" name="{{$_feature}}-{{$components.6}}" value="{{$notif_threshold}}" {{if $is_inherited}}disabled{{/if}}
                 size="3" data-ignore="1" onchange="updateConfigValues('{{$_feature}}');" />
        {{elseif $type == 'status' || $type == 'date'}}
          <select name="{{$_feature}}-{{$components.6}}" data-ignore="1" onchange="updateConfigValues('{{$_feature}}');"
                  {{if $is_inherited}}disabled{{/if}}>
            {{foreach from=$options item=_option}}
              <option value="{{$_option}}" {{if $notif_threshold == $_option}}selected{{/if}}>
                {{tr}}{{$_class}}.{{$field}}.{{$_option}}{{/tr}}
              </option>
            {{/foreach}}
          </select>
        {{elseif $type == 'specific'}}
          {{* Nothing *}}
        {{/if}}
      </td>
    </tr>
  </table>
{{else}}
  <table class="layout">
    <tr>
      <td style="width: 9%; text-align: center;">
        <i class="far fa-chart-bar fa-lg" title="{{tr}}common-action-Display{{/tr}}"></i>
      </td>

      <td style="width: 9%; text-align: center;">
        {{mb_include module=system template=inc_vw_bool_icon value=$display}}
      </td>

      <td style="width: 9%; text-align: center;">
        <i class="fa fa-eye fa-lg" title="{{tr}}CMonitorAlert-Visual alert{{/tr}}"></i>
      </td>

      <td style="width: 9%; text-align: center;">
        {{mb_include module=system template=inc_vw_bool_icon value=$visual}}
      </td>

      <td style="width: 9%; text-align: right;">
        {{$visual_threshold}}
      </td>

      <td style="width: 9%; text-align: center;">
        <i class="fa fa-volume-up fa-lg" title="{{tr}}CMonitorAlert-Audio alert{{/tr}}"></i>
      </td>

      <td style="width: 9%; text-align: center;">
        {{mb_include module=system template=inc_vw_bool_icon value=$audio}}
      </td>

      <td style="width: 9%; text-align: right;">
        {{$audio_threshold}}
      </td>

      <td style="width: 9%; text-align: center;">
        <i class="fa fa-envelope" title="{{tr}}common-Notification{{/tr}}"></i>
      </td>

      <td style="width: 9%; text-align: center;">
        {{mb_include module=system template=inc_vw_bool_icon value=$notif}}
      </td>

      <td style="width: 9%; text-align: right;">
        {{$notif_threshold}}
      </td>
    </tr>
  </table>
{{/if}}
