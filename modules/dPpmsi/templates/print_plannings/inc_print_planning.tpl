{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main">
  <tr>
    <th>
      <a href="#" onclick="window.print()">
        Planning 
        {{mb_include module=system template=inc_interval_date from=$filter->_date_min to=$filter->_date_max}}
      </a>
    </th>
  </tr>
  {{foreach from=$plagesop item=_plage}}
  <tr>
    <td class="text">
      {{if $_plage->_id}}
      <strong>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_plage->_ref_chir}} 
        &ndash;
        {{$_plage->_ref_salle}}
      </strong>
      de {{mb_value object=$_plage field=debut}} 
      à  {{mb_value object=$_plage field=fin}}
      le {{mb_value object=$_plage field=date}}
    
      {{if $_plage->anesth_id}}
        &ndash; Anesthesiste : <strong>Dr {{$_plage->_ref_anesth}}</strong>
      {{/if}}
      
      {{else}}
      <strong>Liste des urgences hors plage</strong>
      {{/if}}
    </td>
  </tr>
  <tr>
    <td>
    </td>
  </tr>
      
  <tr>
    <td>
      <table class="tbl">
        <tr>
          {{if !$_plage->_id}}
          <th class="title" colspan="2">Urgence</th>
          {{/if}}
          <th class="title" colspan="2">Patient</th>
          <th class="title" colspan="4">Sejour</th>
          <th class="title" colspan="4">Intervention (x {{$_plage->_ref_operations|@count}})</th>
        </tr>
        <tr>
          {{if !$_plage->_id}}
          <!-- Cas des urgences -->
          <th class="narrow">Date</th>
          <th style="width: 8em;">Praticien</th>
          {{/if}}
          
          <!-- Patient -->
          <th style="width: 12em;">Nom - Prénom</th>
          <th class="narrow">Naissance</th>

          <!-- Sejour -->
          <th class="narrow">Entree</th>
          <th class="narrow">Sortie</th>
          <th class="narrow">Chambre</th>
          <th style="width: 2em;">DP</th>
          
          <!-- Intervention -->
          <th class="narrow">Heure</th>
          <th style="width: 8em;">Libellé</th>
          <th class="narrow">Codes<br/>prévus</th>
          <th style="width: 16em;">Codage au bloc</th>
        </tr>

        {{foreach from=$_plage->_ref_operations item=_operation}}
        {{assign var=sejour value=$_operation->_ref_sejour}}
        {{assign var=patient value=$sejour->_ref_patient}}
        <tr>
          {{if !$_plage->_id}}
          <!-- Cas des urgences -->
          <td>{{mb_value object=$_operation field=date}}</td>
          <td class="text">{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_operation->_ref_chir}}</td>
          {{/if}}
          <!-- Patient -->
          <td class="text">
            <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
              {{$patient->_view}}
            </span>
          </td>
          <td>
            {{mb_value object=$patient field=naissance}}
          </td>
      
          <!-- Sejour -->
          <td class="text">
            <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}');">
              {{mb_value object=$sejour field=entree}}
            </span>
          </td>
          <td class="text">
            {{mb_value object=$sejour field=sortie}}
          </td>
          <td class="text">
            {{mb_include module=hospi template=inc_placement_sejour sejour=$sejour which=curr}}
          </td>
          <td>
            {{$sejour->DP}}
          </td>

          <!-- Intervention -->
          <td>
            {{$_operation->_datetime|date_format:$conf.time}}
          </td>
          <td class="text">
            <span onmouseover="ObjectTooltip.createEx(this, '{{$_operation->_guid}}');">
              {{$_operation->libelle}}
            </span>
          </td>
          <td>
            {{foreach from=$_operation->_ext_codes_ccam item=_code}}
              <div>{{$_code->code}}</div>
            {{/foreach}}
          </td>
          <td class="text">
            {{foreach from=$_operation->_ref_actes_ccam item=_acte}}
              {{mb_include module=salleOp template=inc_view_acte_ccam_compact acte=$_acte}}
            {{/foreach}}
            {{foreach from=$_operation->_ref_actes_ngap item=_acte}}
              {{mb_include module=cabinet template=inc_view_acte_ngap_compact acte=$_acte}}
            {{/foreach}}
          </td>
        </tr>
        {{/foreach}}
      </table>
    </td>
  </tr>
  {{/foreach}}
</table>