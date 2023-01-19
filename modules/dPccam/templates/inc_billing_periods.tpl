{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $codable->_class == 'COperation' || $codable->_class == 'CConsultation'}}
  {{assign var=sejour value=$codable->_ref_sejour}}
{{elseif $codable->_class == 'CSejour'}}
  {{assign var=sejour value=$codable}}
{{/if}}

<div class="small-info">
  {{tr}}CBillingPeriod-msg-codage_blocked{{/tr}}
  <ul>
    {{foreach from=$sejour->_ref_billing_periods item=_period}}
      {{if $_period->period_statement != '0'}}
        <li>{{tr var1=$_period->period_start|date_format:$conf.date var2=$_period->period_end|date_format:$conf.date}}CBillingPeriod-msg-period_statement.{{$_period->period_statement}}{{/tr}}</li>
      {{/if}}
    {{/foreach}}
  </ul>
</div>