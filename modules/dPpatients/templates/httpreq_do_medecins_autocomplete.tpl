{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
  {{foreach from=$matches item=match}}
    <li id="match-{{$match->_id}}" data-id="{{$match->_id}}">
      <strong class="view">{{$match->_view|emphasize:$keywords}}</strong><br />
      {{if $match->adresse || !$match->_count.medecins_exercices_places}}
        <small>{{$match->cp}} {{$match->ville|emphasize:$keywords}} - {{$match->adresse|emphasize:$keywords}} - {{$match->disciplines|@truncate:25}}</small>
      {{/if}}
    </li>

    {{foreachelse}}
      <li>
        {{tr}}CMedecin-Info-Search-empty-use-chose-button{{/tr}}
      </li>
  {{/foreach}}
</ul>
