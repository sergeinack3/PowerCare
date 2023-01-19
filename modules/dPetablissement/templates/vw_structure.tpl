{{*
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h1>{{$etab->_view}}</h1>

<table class="form">
  <tr>
    <td colspan="4">
      {{mb_value object=$etab field=raison_sociale}}
      {{mb_value object=$etab field=adresse}}
      {{mb_value object=$etab field=cp}} - {{mb_value object=$etab field=ville}}
    </td>
  </tr>
  <tr>
    <th style="width: 25%">{{mb_label object=$etab field=tel}}</th>
    <td style="width: 25%">{{mb_value object=$etab field=tel}}</td>
    <th style="width: 25%">{{mb_label object=$etab field=siret}}</th>
    <td style="width: 25%">{{mb_value object=$etab field=siret}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$etab field=fax}}</th>
    <td>{{mb_value object=$etab field=fax}}</td>
    <th>{{mb_label object=$etab field=finess}}</th>
    <td>{{mb_value object=$etab field=finess}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$etab field=domiciliation}}</th>
    <td>{{mb_value object=$etab field=domiciliation}}</td>
    <th>{{mb_label object=$etab field=ape}}</th>
    <td>{{mb_value object=$etab field=ape}}</td>
  </tr>
</table>

<h1>Services d'hospitalisation</h1>

{{foreach from=$services item=_service}}
  <h2>#{{$_service->_id}} {{$_service->_view}}</h2>
  {{mb_value object=$_service field=description}}
  {{if $_service->hospit_jour}}
    {{mb_label object=$_service field=hospit_jour}}<br/>
  {{/if}}
  {{if $_service->urgence}}
    {{mb_label object=$_service field=urgence}}<br/>
  {{/if}}
  {{if $_service->uhcd}}
    {{mb_label object=$_service field=uhcd}}<br/>
  {{/if}}
  {{if $_service->neonatalogie}}
    {{mb_label object=$_service field=neonatalogie}}
  {{/if}}
  <table class="tbl">
    <tr>
      <th class="narrow">#</th>
      <th>{{tr}}CLit{{/tr}}</th>
      <th class="narrow">#</th>
      <th>{{tr}}CChambre{{/tr}}</th>
      <th>{{mb_title class=CLit field=nom_complet}}</th>
      <th>{{mb_title class=CChambre field=caracteristiques}}</th>
    </tr>
    {{foreach from=$_service->_ref_chambres item=_chambre}}
    {{foreach from=$_chambre->_ref_lits item=_lit}}
    <tr>
      <td style="text-align: right;" class="compact">{{$_lit->_id}}</td>
      <td>{{mb_value object=$_lit     field=nom}}</td>
      <td style="text-align: right;" class="compact">{{$_chambre->_id}}</td>
      <td>{{mb_value object=$_chambre field=nom}}</td>
      <td>{{mb_value object=$_lit     field=nom_complet}}</td>
      <td>
        {{mb_value object=$_chambre field=caracteristiques}}
        {{if $_chambre->is_waiting_room}}
          <li>{{mb_label object=$_chambre field=is_waiting_room}}</li>
        {{/if}}
        {{if $_chambre->is_examination_room}}
          <li>{{mb_label object=$_chambre field=is_examination_room}}</li>
        {{/if}}
        {{if $_chambre->is_sas_dechoc}}
          <li>{{mb_label object=$_chambre field=is_sas_dechoc}}</li>
        {{/if}}
      </td>
    </tr>
    {{/foreach}}
    {{/foreach}}
  </table>
{{/foreach}}

<h1>Blocs opératoires</h1>

{{foreach from=$blocs item=_bloc}}
  <h2>{{$_bloc}}</h2>
  {{mb_label object=$_bloc field=type}} : {{mb_value object=$_bloc field=type}}
  <table class="tbl">
    <tr>
      <th class="narrow">#</th>
      <th>{{tr}}CSalle{{/tr}}</th>
    </tr>
    {{foreach from=$_bloc->_ref_salles item=_salle}}
    <tr>
      <td class="compact" style="text-align: right;">{{$_salle->_id}}</td>
      <td>{{mb_value object=$_salle field=nom}}</td>
    </tr>
    {{/foreach}}
  </table>
{{/foreach}}
