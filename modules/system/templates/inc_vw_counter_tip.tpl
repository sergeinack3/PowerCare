{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=warning value=0}}
{{mb_default var=show_span value=0}}
{{mb_default var=min_value value=1}}
{{mb_default var=style_span value=""}}

{{if $app->user_prefs.showCounterTip && ($show_span || ($count && ($count > $min_value)))}}
  {{if $warning}}
    <span class="countertip" style="color: red; {{$style_span}}">
      <strong>{{$count}}</strong>
    </span>
  {{else}}
    <span class="countertip" style="{{$style_span}}">
      {{$count}}
    </span>
  {{/if}}
{{/if}}
