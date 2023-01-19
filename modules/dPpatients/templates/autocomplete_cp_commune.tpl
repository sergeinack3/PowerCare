{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
  {{foreach from=$matches key=_key item=_match}}
    <li data-pays="{{$_match.pays}}" data-numeric="{{$_match.numerique}}">
      <span class="cp"><strong>{{$_match.code_postal|emphasize:$keyword}}</strong></span>
      &ndash;
      <span class="commune">{{$_match.commune|emphasize:$keyword}}</span>
      <div style="color: #888; padding-left: 1em;">
        <small>{{if $_match.departement}}{{$_match.departement}} - {{/if}}{{$_match.pays}}</small>
      </div>
      {{if $_match.INSEE}}
        <div style="color: #888;">{{tr}}INSEE{{/tr}} : <span class="insee">{{$_match.INSEE}}</span></div>
      {{else}}
        <div style="color: #888;">{{tr}}CPatient-pays_naissance_insee{{/tr}} : <span class="insee">{{$_match.code_insee}}</span></div>
      {{/if}}
    </li>
  {{/foreach}}
</ul>
