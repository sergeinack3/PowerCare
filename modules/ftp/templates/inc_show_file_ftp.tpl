{{*
 * @package Mediboard\Ftp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$content}}
  <div class="small-warning">{{tr}}CExchangeSource-msg-Unknow file type{{/tr}}</div>
  {{mb_return}}
{{/if}}

{{if $image}}
  <img src="data:image/{{$extension}};base64,{{$content}}" />
{{else}}
  <pre>{{$content}}</pre>
{{/if}}
