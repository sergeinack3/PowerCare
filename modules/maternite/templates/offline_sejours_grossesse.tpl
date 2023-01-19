{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<style>
  @media print {
    div.modal_view {
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
      font-size: inherit !important;
    }
  }
</style>

<script>
  // La div du dossier qui a été passé dans la fonction Modal.open()
  // a du style supplémentaire, qu'il faut écraser lors de l'impression
  // d'un dossier seul.
  printOneDossier = function(container) {
    Element.print($(container).childElements());
  };
</script>

<table class="tbl">
  <thead>
    <tr>
      <th class="title" colspan="5">
        <button type="button" class="not-printable print" style="float: right;" onclick="window.print();">{{tr}}Print{{/tr}}</button>
        {{$grossesses|@count}} grossesse(s) avec un terme prévu entre le {{$date_min|date_format:$conf.date}} et {{$date_max|date_format:$conf.date}}
      </th>
    </tr>

    <tr>
      <th>{{tr}}CPatient|f{{/tr}}</th>
      <th>{{mb_title class=CGrossesse field=_semaine_grossesse}}</th>
      <th>{{mb_title class=CGrossesse field=terme_prevu}}</th>
      <th>Séjours / consults</th>
      <th style="width: 30%">{{tr}}common-Action|pl{{/tr}}</th>
    </tr>
  </thead>

  {{foreach from=$grossesses item=_grossesse}}
      {{assign var=patiente        value=$_grossesse->_ref_parturiente}}
      {{assign var=dossier_perinat value=$_grossesse->_ref_dossier_perinat}}
      {{assign var=echographies    value=$_grossesse->_ref_surv_echographies}}
      {{assign var=consultations   value=$_grossesse->_ref_consultations}}

      <tr>
        <td class="text" style="page-break-inside: avoid;">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$patiente->_guid}}')">
            {{$patiente}}
          </span>
        </td>
        <td class="text">
          {{$_grossesse->_semaine_grossesse}} SA +{{$_grossesse->_reste_semaine_grossesse}} j
        </td>
        <td class="text">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_grossesse->_guid}}')">
            {{$_grossesse->terme_prevu|date_format:$conf.date}}
          </span>
        </td>
        <td class="text">
          {{$_grossesse->_nb_ref_sejours}} {{tr}}CSejour{{/tr}}(s)
          / {{$_grossesse->_ref_consultations|@count}} {{tr}}CConsultation{{/tr}}(s)
          {{if $_grossesse->_ref_consultations|@count && $_grossesse->_ref_consultations_anesth|@count}}
            dont {{$_grossesse->_ref_consultations_anesth|@count}} {{tr}}CConsultAnesth{{/tr}}
          {{/if}}
        </td>
        <td class="text">
          <button type="button" class="print compact not-printable"
                  onclick="printOneDossier('fiche_anesth_{{$patiente->_guid}}');">
            {{tr}}CAnesthPerop-action-Anesthesia sheet{{/tr}}
          </button>
          <button type="button" class="print compact not-printable"
                  onclick="printOneDossier('suivi_grossesse_{{$patiente->_guid}}');">{{tr}}CSuiviGrossesse{{/tr}}</button>
          <button type="button" class="print compact not-printable"
                  onclick="printOneDossier('fiche_synthese_{{$patiente->_guid}}');">
            {{tr}}CGrossesse-action-Summary sheet{{/tr}}
          </button>
        </td>
      </tr>
  {{foreachelse}}
    <tr>
      <td colspan="5" class="empty">{{tr}}CGrossesse.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
<hr style="border: 0; page-break-after: always;" />

{{foreach from=$grossesses item=_grossesse name=list_grossesses}}
  {{assign var=grossesse_id    value=$_grossesse->_id}}
  {{assign var=patiente        value=$_grossesse->_ref_parturiente}}
  {{assign var=dossier_perinat value=$_grossesse->_ref_dossier_perinat}}
  {{assign var=echographies    value=$_grossesse->_ref_surv_echographies}}
  {{assign var=consultations   value=$_grossesse->_ref_consultations}}

  {{* Fiches d'anesthésie *}}
  <div id="fiche_anesth_{{$patiente->_guid}}" style="display: none;" class="modal_view">
    {{if array_key_exists($grossesse_id, $fiches_anesth)}}
      {{foreach from=$fiches_anesth.$grossesse_id item=_fiche_anesth name=fiches_anesths}}
        {{$_fiche_anesth|smarty:nodefaults}}

        {{if !$smarty.foreach.fiches_anesths.last}}
          <hr style="border: 0; page-break-after: always;" />
        {{/if}}
      {{/foreach}}
    {{/if}}
  </div>

  {{* Suivi de grossesse *}}
  <div id="suivi_grossesse_{{$patiente->_guid}}" style="display: none;" class="modal_view">
    {{$suivi_grossesse.$grossesse_id|smarty:nodefaults}}
  </div>

  {{* Fiche de synthèse *}}
  <div id="fiche_synthese_{{$patiente->_guid}}" style="display: none;" class="modal_view">
    {{$fiche_synthese.$grossesse_id|smarty:nodefaults}}
  </div>

  {{if !$smarty.foreach.list_grossesses.last}}
    <hr style="border: 0; page-break-after: always;" />
  {{/if}}
{{/foreach}}
