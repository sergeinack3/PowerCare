{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $from != $to}}
  De {{$from|date_format:$conf.time}}
  à  {{$to|date_format:$conf.time}}
{{else}}
  à  {{$to|date_format:$conf.time}}
{{/if}}
