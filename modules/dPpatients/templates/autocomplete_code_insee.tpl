{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
  {{foreach from=$matches key=_key item=_match}}
    {{if is_object($_match)}}
      {{if $_match|instanceof:"Ox\Mediboard\Patients\CPaysInsee"}}
        <li data-class="CPaysInsee" data-insee="{{$_match->code_insee}}" data-country="{{$_match->nom_fr}}">
          <div>
            <span class="me-font-weight-bold">{{$_match->nom_fr}} ({{$_match->alpha_3}})</span>
          </div>
          <div style="color: #888;">
            <span class="insee">{{tr}}CPatient-_code_insee-court{{/tr}} : {{$_match->code_insee|emphasize:$keyword}}</span>
          </div>
        </li>
      {{/if}}
    {{else}}
      <li data-insee="{{$_match.INSEE}}" data-place="{{$_match.commune}}" data-zipcode="{{$_match.code_postal}}" data-country="{{$_match.pays}}">
        <div>
          <span class="commune me-font-weight-bold">{{$_match.commune}} - {{$_match.code_postal}}</span>
        </div>
        <div style="color: #888;">
          <small>{{if $_match.departement}}{{$_match.departement}} - {{/if}}{{$_match.pays}}</small>
        </div>
        <div style="color: #888;">
          <span class="insee">{{tr}}CPatient-_code_insee-court{{/tr}} : {{$_match.INSEE|emphasize:$keyword}}</span>
        </div>
      </li>
    {{/if}}
  {{/foreach}}
</ul>
