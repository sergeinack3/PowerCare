{{*
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<pre>HTTP {{$response_code}} {{$response_message}}</pre>

{{if $response}}
  {{$response|highlight:$lang}}
{{/if}}




