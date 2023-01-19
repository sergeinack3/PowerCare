{{*
 * @package Mediboard\Qualite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  {{if !$fiche->valid_user_id}}
  <th colspan="2" class="title modify">
    {{tr}}_CFicheEi_valid{{/tr}} {{$fiche->_view}}
    {{else}}
  <th colspan="2" class="title">
    {{tr}}_CFicheEi_view{{/tr}} {{$fiche->_view}}
    {{/if}}
  </th>
</tr>
<tr>
  <th>{{tr}}CFicheEi-type_incident-court{{/tr}}</th>
  <td>
    <strong>
      {{tr}}CFicheEi.type_incident.{{$fiche->type_incident}}-long{{/tr}}
    </strong>
    <br />{{$fiche->date_incident|date_format:"%A %d %B %Y à %Hh%M"}}
  </td>
</tr>
<tr>
  <th>{{tr}}CFicheEi-user_id{{/tr}}</th>
  <td class="text">
    {{if $conf.dPqualite.CFicheEi.mode_anonyme && !$modules.dPqualite->_can->admin && ($fiche->_ref_user->user_id != $app->user_id)}}
      Anonyme
    {{else}}
      {{$fiche->_ref_user->_view}}
      <br />
      {{$fiche->_ref_user->_ref_function->_view}}
    {{/if}}
  </td>
</tr>
<tr>
  <th>{{tr}}CFicheEi-elem_concerne-court{{/tr}}</th>
  <td class="text">
    {{tr}}CFicheEi.elem_concerne.{{$fiche->elem_concerne}}{{/tr}}<br />
    {{$fiche->elem_concerne_detail|nl2br}}
  </td>
</tr>
<tr>
  <th>{{tr}}CFicheEi-lieu{{/tr}}</th>
  <td class="text">
    {{$fiche->lieu}}
  </td>
</tr>
<tr>
  <th colspan="2" class="category">{{tr}}CFicheEi-evenements{{/tr}}</th>
</tr>
{{foreach from=$catFiche item=currEven key=keyEven}}
  <tr>
    <th><strong>{{$keyEven}}</strong></th>
    <td>
      <ul>
        {{foreach from=$currEven item=currItem}}
          <li>{{$currItem->nom}}</li>
        {{/foreach}}
      </ul>
    </td>
  </tr>
{{/foreach}}
<tr>
  <th colspan="2" class="category">{{tr}}_CFicheEi-infoscompl{{/tr}}</th>
</tr>
{{if $fiche->autre}}
  <tr>
    <th>{{tr}}CFicheEi-autre{{/tr}}</th>
    <td class="text">{{$fiche->autre|nl2br}}</td>
  </tr>
{{/if}}
<tr>
  <th>{{tr}}CFicheEi-descr_faits{{/tr}}</th>
  <td class="text">{{$fiche->descr_faits|nl2br}}</td>
</tr>
<tr>
  <th>{{tr}}CFicheEi-mesures{{/tr}}</th>
  <td class="text">{{$fiche->mesures|nl2br}}</td>
</tr>
<tr>
  <th>{{tr}}CFicheEi-descr_consequences{{/tr}}</th>
  <td class="text">{{$fiche->descr_consequences|nl2br}}</td>
</tr>
<tr>
  <th>{{tr}}CFicheEi-suite_even{{/tr}}</th>
  <td class="text">
    {{tr}}CFicheEi.suite_even.{{$fiche->suite_even}}{{/tr}}
    {{if $fiche->suite_even=="autre"}}
      <br />
      {{$fiche->suite_even_descr|nl2br}}
    {{/if}}
  </td>
</tr>
<tr>
  <th>{{tr}}CFicheEi-deja_survenu-court{{/tr}}</th>
  <td>
    {{tr}}CFicheEi.deja_survenu.{{$fiche->deja_survenu}}{{/tr}}
  </td>
</tr>
<tr>
  <th colspan="2" class="category">{{tr}}_CFicheEi_validqualite{{/tr}}</th>
</tr>
{{if $fiche->date_validation}}
  <tr>
    <th>{{tr}}CFicheEi-degre_urgence{{/tr}}</th>
    <td>{{tr}}CFicheEi.degre_urgence.{{$fiche->degre_urgence}}{{/tr}}</td>
  </tr>
  {{if $can->admin}}
    <tr>
      <th>{{tr}}CFicheEi-gravite{{/tr}}</th>
      <td>
        {{tr}}CFicheEi.gravite.{{$fiche->gravite}}{{/tr}}
      </td>
    </tr>
    <tr>
      <th>{{tr}}CFicheEi-vraissemblance{{/tr}}</th>
      <td>
        {{tr}}CFicheEi.vraissemblance.{{$fiche->vraissemblance}}{{/tr}}
      </td>
    </tr>
  {{/if}}
  <tr>
    <th>{{tr}}CFicheEi-_criticite{{/tr}}</th>
    <td>
      {{tr}}CFicheEi._criticite.{{$fiche->_criticite}}{{/tr}}
    </td>
  </tr>
  <tr>
    <th>{{tr}}CFicheEi-plainte{{/tr}}</th>
    <td>
      {{tr}}CFicheEi.plainte.{{$fiche->plainte}}{{/tr}}
    </td>
  </tr>
  <tr>
    <th>{{tr}}CFicheEi-commission{{/tr}}</th>
    <td>
      {{tr}}CFicheEi.commission.{{$fiche->commission}}{{/tr}}
    </td>
  </tr>
  <tr>
    <th>{{tr}}_CFicheEi_validBy{{/tr}}</th>
    <td>
      {{$fiche->_ref_user_valid->_view}}
      <br />{{$fiche->date_validation|date_format:"%d %b %Y à %Hh%M"}}
    </td>
  </tr>
  <tr>
    <th>{{tr}}_CFicheEi_sendTo{{/tr}}</th>
    <td>
      {{$fiche->_ref_service_valid->_view}}
    </td>
  </tr>
{{/if}}

{{if $fiche->service_date_validation}}
  <tr>
    <th colspan="2" class="category">{{tr}}_CFicheEi_validchefserv{{/tr}}</th>
  </tr>
  <tr>
    <th>{{tr}}CFicheEi-service_valid_user_id-court{{/tr}}</th>
    <td>
      {{$fiche->_ref_service_valid->_view}}
      <br />{{$fiche->service_date_validation|date_format:"%d %b %Y à %Hh%M"}}
    </td>
  </tr>
  <tr>
    <th>{{tr}}CFicheEi-service_actions-court{{/tr}}</th>
    <td class="text">{{$fiche->service_actions|nl2br}}</td>
  </tr>
  <tr>
    <th>{{tr}}CFicheEi-service_descr_consequences{{/tr}}</th>
    <td class="text">{{$fiche->service_descr_consequences|nl2br}}</td>
  </tr>
{{/if}}

{{if $fiche->qualite_date_validation}}
  <tr>
    <th colspan="2" class="category">{{tr}}_CFicheEi_validservqualite{{/tr}}</th>
  </tr>
  <tr>
    <th>{{tr}}_CFicheEi_validBy{{/tr}}</th>
    <td>
      {{$fiche->_ref_qualite_valid->_view}}
      <br />{{$fiche->qualite_date_validation|date_format:"%d %b %Y à %Hh%M"}}
    </td>
  </tr>
  {{if $fiche->qualite_date_verification}}
    <tr>
      <th>{{tr}}CFicheEi-qualite_date_verification{{/tr}}</th>
      <td>{{$fiche->qualite_date_verification|date_format:"%d %b %Y"}}</td>
    </tr>
  {{/if}}
  {{if $fiche->qualite_date_controle}}
    <tr>
      <th>{{tr}}CFicheEi-qualite_date_controle{{/tr}}</th>
      <td>{{$fiche->qualite_date_controle|date_format:"%d %b %Y"}}</td>
    </tr>
  {{/if}}
{{/if}}

{{if !$can->admin}}
  <tr>
    <td colspan="2" class="button">
      {{$fiche->_etat_actuel}}
    </td>
  </tr>
{{/if}}