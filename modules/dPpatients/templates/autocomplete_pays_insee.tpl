{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
    {{foreach from=$matches item=_match}}
      <li data-name="{{$_match->nom_fr}}" data-insee="{{$_match->code_insee}}" data-numeric="{{$_match->numerique}}">
        <div>
          <span class="me-font-weight-bold">{{$_match->nom_fr|emphasize:$pays}}</span>
        </div>
        {{if $_match->numerique != 250}}
          <div class="me-margin-top-2" style="color: #888;">
            <span class="insee">{{tr}}CPatient-pays_naissance_insee{{/tr}} : {{$_match->code_insee}}</span>
          </div>
        {{/if}}
      </li>
    {{/foreach}}
</ul>
