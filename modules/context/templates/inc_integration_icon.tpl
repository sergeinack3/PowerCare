{{*
 * @package Mediboard\Context
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $integration->icon_url|strpos:"fa" === 0}}
  <i class="{{$integration->icon_url}}"></i>
{{else}}
  <img src="{{$integration->icon_url}}" height="16" />
{{/if}}
