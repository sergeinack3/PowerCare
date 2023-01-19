{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$firstname_tbl_installed}}
  <div class="small-error" id="import-table-name">
    {{tr}}system-Name table does not contain data{{/tr}}
    <button class="import" onclick="ObjectPseudonymiser.goToTablePrenom();">{{tr}}system-Import table name{{/tr}}</button>
  </div>
{{/if}}

<div class="small-info">
  L'utilisateur courant ainsi que l'utilisateur "admin" ne seront pas modifiés.

  <br/>

  {{tr}}system-msg-Pseudonymise fields to modify{{/tr}} :
  <ul>
    <li>{{tr}}CUser-user_last_name{{/tr}} : Modifié pour un prénom pris au hasard dans une liste (~12000 prénoms)</li>
    <li>{{tr}}CUser-user_username{{/tr}} : Modifié pour un une chaîne alphanumérique aléatoire de 12 caractères</li>
  </ul>

  <br/>

  {{if $_fields}}
    {{tr}}system-msg-Pseudonymise fields to empty{{/tr}} :

    <ul>
      {{foreach from=$_fields item=_field}}
        <li>{{tr}}{{$_class}}-{{$_field}}{{/tr}}</li>
      {{/foreach}}
      <li>{{tr}}CMediusers-adeli{{/tr}}</li>
      <li>{{tr}}CMediusers-rpps{{/tr}}</li>
      <li>{{tr}}CMediusers-inami{{/tr}}</li>
      <li>{{tr}}CMediusers-cps{{/tr}}</li>
      <li>{{tr}}CMediusers-ean{{/tr}}</li>
      <li>{{tr}}CMediusers-rcc{{/tr}}</li>
    </ul>
  {{/if}}
</div>