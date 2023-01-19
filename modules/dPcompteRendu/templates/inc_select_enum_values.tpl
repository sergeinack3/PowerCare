{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$list key=_key item=_locale}}
  {{if $_key == '0'}}
  <option value="">&mdash; {{tr}}None{{/tr}}
</option>
  {{else}}
  <option value="{{if $_key !== '0'}}{{$_key}}{{/if}}">{{$_locale}}</option>
  {{/if}}
{{/foreach}}