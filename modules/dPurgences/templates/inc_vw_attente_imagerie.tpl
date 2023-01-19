{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<td>
  {{foreach from=$affectations item=_affectation}}
    {{if $_affectation->_ref_service->radiologie == '1'}}
        {{$_affectation->entree|date_format:$conf.time}}<br/>
    {{/if}}
  {{/foreach}}
</td>
<td>
  {{foreach from=$affectations item=_affectation}}
    {{if $_affectation->_ref_service->radiologie == '1'}}
      {{if $_affectation->sortie !== $sortie}}{{$_affectation->sortie|date_format:$conf.time}}{{else}}-{{/if}}<br/>
    {{/if}}
  {{/foreach}}
</td>