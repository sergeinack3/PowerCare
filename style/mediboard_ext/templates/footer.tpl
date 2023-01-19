{{*
 * @package Mediboard\Style\Mediboard
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $infosystem && !$offline && $show_performance}}
  {{mb_include style=mediboard_ext template=performance}}
{{else}}
  {{if $conf.locale_warn}}
    <ul id="performance">
      <li class="performance-l10n" id="i10n-alert" onclick="Localize.showForm()" title="{{tr}}system-msg-unlocalized_warning{{/tr}}">
        0
      </li>
    </ul>
  {{/if}}
{{/if}}
</div>
{{mb_include style=mediboard_ext template=common_end nodebug=true}}
