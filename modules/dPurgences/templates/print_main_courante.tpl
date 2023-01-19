{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$offline}}
  <script>
    Main.add(window.print);
  </script>
{{/if}}

<style type="text/css">
  @media print {
    div.dossier {
      display: block !important;
      height: auto !important;
      width: 100% !important;
      font-size: 8pt !important;
      left: auto !important;
      top: auto !important;
      position: static !important;
    }
    table {
      width: 100% !important;
      font-size: inherit; !important
    }
  }
</style>

{{assign var=print_gemsa value="dPurgences Print gemsa"|gconf}}

<div id="main-courante-container">
  <table class="main">
    <tr>
      <th>
        {{if $offline}}
          <button style="float: left;" onclick="$('main-courante-container').print()" class="print not-printable">
            {{tr}}Main_courante{{/tr}}
          </button>
          <button style="float: left;" onclick="window.print()" class="print not-printable">Dossiers</button>
          <span style="float: right;">
            {{$dtnow|date_format:$conf.datetime}}
          </span>
        {{/if}}
         <a href="#print" onclick="printPage(this)">
          Résumé des Passages aux Urgences du
          {{$date|date_format:$conf.longdate}}
          <br /> Total: {{$sejours|@count}} RPU
        </a>
      </th>
    </tr>
    <tr>
      <td>
        <table class="tbl">
          {{mb_include module=urgences template=inc_print_header_main_courante}}
          {{foreach from=$sejours item=sejour}}
            {{mb_include module=urgences template=inc_print_main_courante}}
          {{/foreach}}
        </table>
      </td>
    </tr>
  </table>

  <table class="tbl">
    <tr>
      <th class="title" colspan="1">
        Statistiques d'entrée
        <small>({{$stats.entree.total}})</small>
      </th>
      <th class="title" colspan="2">
        Statistiques de sorties
        <small>({{$stats.sortie.total}})</small>
      </th>
    </tr>

    <tr>
      <th>{{mb_title class=CPatient field=_age}}</th>
      <th>
        {{mb_title class=CSejour field=etablissement_sortie_id}}
        <small>({{$stats.sortie.transferts_count}})</small>
      </th>
      <th>
        {{mb_title class=CSejour field=service_sortie_id}}
        <small>({{$stats.sortie.mutations_count}})</small>
      </th>
    </tr>

    <tr>
      <td>
        <ul>
          <li>
            Patients de moins de 1 ans :
            <strong>{{$stats.entree.less_than_1}}</strong>
          </li>
          <li>
            Patients de 75 ans ou plus :
            <strong>{{$stats.entree.more_than_75}}</strong>
          </li>
        </ul>
      </td>

      <td>
        <ul>
          {{foreach from=$stats.sortie.etablissements_transfert item=_etablissement_transfert}}
          <li>
            {{$_etablissement_transfert.ref}} :
            <strong>{{$_etablissement_transfert.count}}</strong>
          </li>
          {{foreachelse}}
          <li class="empty">{{tr}}None{{/tr}}</li>
          {{/foreach}}
        </ul>
      </td>

      <td>
        <ul>
          {{foreach from=$stats.sortie.services_mutation item=_service_mutation}}
          <li>
            {{$_service_mutation.ref}} :
            <strong>{{$_service_mutation.count}}</strong>
          </li>
          {{foreachelse}}
          <li class="empty">{{tr}}None{{/tr}}</li>
          {{/foreach}}
        </ul>
      </td>
    </tr>
  </table>
</div>

{{if $offline}}
  {{foreach from=$sejours item=sejour}}
    {{assign var=rpu value=$sejour->_ref_rpu}}
    {{if $rpu->_id}}
      {{assign var=patient value=$sejour->_ref_patient}}
      {{assign var=consult value=$rpu->_ref_consult}}
      {{assign var=sejour_id value=$sejour->_id}}
      <div id="modal-{{$sejour->_id}}" style="display: none; height: 90%; min-width: 700px; overflow: auto; page-break-before: always;" class="dossier">
        <button style="float: right" class="cancel not-printable" onclick="Control.Modal.close()">{{tr}}Close{{/tr}}</button>
        <button style="float: right" class="print not-printable" onclick="$(this).next('.content').print()">{{tr}}Print{{/tr}}</button>
        <div class="content">
          {{$offlines.$sejour_id|smarty:nodefaults}}
        </div>
      </div>
    {{/if}}
  {{/foreach}}
{{/if}}