{{*
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $type == 'web_service'}}
  <option value="">&mdash; Liste des web services</option>
  {{foreach from=$web_services item=_web_service}}
    <option value="{{$_web_service}}" {{if $web_service == $_web_service}}selected="selected"{{/if}}>
      {{$_web_service}}
    </option>
  {{/foreach}}
{{else}}
  <option value="">&mdash; Liste des fonctions</option>
  {{foreach from=$fonctions item=_fonction}}
    <option value="{{$_fonction}}" {{if $fonction == $_fonction}}selected="selected"{{/if}}>
      {{$_fonction}}
    </option>
  {{/foreach}}
{{/if}}
