{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$duration key=_unit item=_value}}
  {{if $_value != 0 && $_unit != "second"}}
    {{$_value}} {{tr}}{{$_unit}}{{if $_value>1}}s{{/if}}{{/tr}}
  {{/if}}

{{/foreach}}