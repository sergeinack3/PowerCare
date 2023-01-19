{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from="A"|range:"Z" item=_lettre}}
  <a href="#1" onclick="showPairesLettre('{{$_lettre}}')" class="page {{if $lettre == $_lettre}}active{{/if}}">
    {{$_lettre}}
  </a>
{{/foreach}}

{{if $lettre}}
  <table class="tbl">
    <tr>
      <th style="width: 80%;">Prénom</th>
      <th>Sexe</th>
    </tr>
    {{foreach from=$paires item=_paire}}
      {{assign var=bg value="#ddd;"}}
      {{if $_paire->sex == "m"}}
        {{assign var=bg value="#eef;"}}
      {{elseif $_paire->sex == "f"}}
        {{assign var=bg value="#fee;"}}
      {{/if}}
      <tr>
        <td style="background-color: {{$bg}};">
          {{$_paire->firstname}}
        </td>
        <td style="background-color: {{$bg}};">
          {{$_paire->sex}}
        </td>
      </tr>
    {{/foreach}}
  </table>
{{else}}
  <div class="small-info">
    Veuillez cliquer sur une lettre pour afficher les prénoms correspondants.
  </div>
{{/if}}