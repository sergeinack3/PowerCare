{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$active_filter key=_filter_label item=_filter_value}}
  {{if $_filter_value !== ""}}
    <div class="tdb-cotation-active-filter">
      <i class="fa fa-filter"></i>
      <div class="tdb-cotation-active-filter-label">{{tr}}{{$_filter_label}}{{/tr}} :</div>
      <div class="tdb-cotation-active-filter-value">{{$_filter_value}}</div>
    </div>
  {{/if}}
{{/foreach}}
