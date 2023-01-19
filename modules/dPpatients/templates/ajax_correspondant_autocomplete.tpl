{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
  {{foreach from=$correspondants item=corresp}}
    <li>
      <strong class="newcode"
              data-id="{{$corresp->_id}}">{{$corresp->nom}} {{if $corresp->surnom}}({{$corresp->surnom}}){{/if}}</strong>
      <br />
      <small>{{$corresp->adresse}} {{$corresp->cp}} {{$corresp->ville}}</small>
      {{if $corresp->date_debut && $corresp->date_fin}}
        <br />
        <small>
          Du {{$corresp->date_debut|date_format:$conf.date}} au {{$corresp->date_fin|date_format:$conf.date}}
        </small>
      {{/if}}
    </li>
  {{/foreach}}
</ul>