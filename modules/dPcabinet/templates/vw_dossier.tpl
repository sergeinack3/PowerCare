{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=pat_selector}}
{{mb_script module=files    script=files}}

<script>
  function printPack(hospi_id, pack_id) {
    if (pack_id) {
      var url = new Url("compteRendu", "edit");
      url.addParam("object_id", hospi_id);
      url.addParam("pack_id", pack_id);
      url.popup(700, 600, "Impression de pack");
    }
  }

  function ZoomAjax(objectClass, objectId, elementClass, elementId, sfn) {
    popFile(objectClass, objectId, elementClass, elementId, sfn);
  }

  Main.add(function() {
    PairEffect.initGroup("consEffect", { bStoreInCookie: false });
    PairEffect.initGroup("operEffect", { bStoreInCookie: false });
  });
</script>

<form name="FrmClass" action="?m={{$m}}" method="get" onsubmit="reloadListFileDossier('load'); return false;">
  <input type="hidden" name="selKey"   value="" />
  <input type="hidden" name="selClass" value="" />
  <input type="hidden" name="selView"  value="" />
  <input type="hidden" name="keywords" value="" />
  <input type="hidden" name="file_id"  value="" />
  <input type="hidden" name="typeVue"  value="0" />
</form>
      
<table class="main">
  <tr>
    <td class="greedyPane" colspan="2">
      <form name="patFrm" action="?" method="get">
      <table class="form">
        <tr><th>Choix du patient :</th>
          <td>
            <input type="hidden" name="m" value="{{$m}}" />
            <input type="hidden" name="patSel" value="{{$patient->patient_id}}" onchange="this.form.submit()" />
            <input type="text" size="40" readonly="readonly" ondblclick="PatSelector.init()" name="patNom" value="{{$patient->_view}}" />
          </td>
          <td class="button">
            <button class="search" type="button" onclick="PatSelector.init()">Chercher</button>
            <script type="text/javascript">
            PatSelector.init = function(){
              this.sForm = "patFrm";
              this.sId   = "patSel";
              this.sView = "patNom";
              this.pop();
            }
            </script>
          </td>
        </tr>
      </table>
      </form>
    </td>
  </tr>
  {{if $patient->patient_id}}
  <tr>
    <td>
      <table class="form">
    <!-- Consultations -->
        <tr>
          <th class="category" colspan="2">Consultations</th>
        </tr>
        {{foreach from=$patient->_ref_consultations item=curr_consult}}
        {{if $curr_consult->_canEdit}}
        <tr id="cons{{$curr_consult->consultation_id}}-trigger">
          <td colspan="2" onclick="setObject( {
              objClass: '{{$curr_consult->_class}}', 
              keywords: '', 
              id: {{$curr_consult->consultation_id|smarty:nodefaults|JSAttribute}}, 
              view:'{{$curr_consult->_view|smarty:nodefaults|JSAttribute}}'} )">
            <strong>
              {{if $curr_consult->_ref_plageconsult->_ref_chir->isPraticien()}}Dr{{/if}} {{$curr_consult->_ref_plageconsult->_ref_chir->_view}} &mdash;
            {{$curr_consult->_ref_plageconsult->date|date_format:$conf.longdate}} &mdash;
            {{$curr_consult->_etat}}
            </strong>
          </td>
        </tr>
        <tbody class="consEffect" id="cons{{$curr_consult->consultation_id}}">
          <tr>
            <td colspan="2">
              <a href="?m=dPcabinet&tab=edit_consultation&selConsult={{$curr_consult->consultation_id}}">
                Voir la consultation
              </a>
            </td>
          </tr>
          <tr>
            <th>Motif :</th>
            <td class="text">{{$curr_consult->motif}}</td>
          </tr>
          {{if $curr_consult->rques}}
          <tr>
            <th>Remarques :</th>
            <td class="text">{{$curr_consult->rques}}</td>
          </tr>
          {{/if}}
          {{if $curr_consult->examen}}
          <tr>
            <th>Examen :</th>
            <td class="text">{{$curr_consult->examen}}</td>
          </tr>
          {{/if}}
          {{if $curr_consult->traitement}}
           <tr>
             <th>Traitement :</th>
             <td class="text">{{$curr_consult->traitement}}</td>
           </tr>
          {{/if}}
          <tr>
            <th>Documents attachés :</th>
            <td id="File{{$curr_consult->_class}}{{$curr_consult->_id}}"></td>
          </tr>
        </tbody>
        {{/if}}
        {{/foreach}}
        
    <!-- Sejours -->
        {{foreach from=$patient->_ref_sejours item=curr_sejour}}
        {{if $curr_sejour->_canEdit}}
        <tr>
          <th class="category" colspan="2">
            Séjour du {{$curr_sejour->entree_prevue|date_format:"%d %B %Y à %Hh%M"}}
            au {{$curr_sejour->sortie_prevue|date_format:"%d %B %Y à %Hh%M"}}
          </th>
        </tr>
        {{foreach from=$curr_sejour->_ref_operations item=curr_op}}
        <tr id="oper{{$curr_op->operation_id}}-trigger">
          <td colspan="2" onclick="setObject( {
              objClass: '{{$curr_op->_class}}', 
              keywords: '', 
              id: {{$curr_op->operation_id|smarty:nodefaults|JSAttribute}}, 
              view:'{{$curr_op->_view|smarty:nodefaults|JSAttribute}}'} )">
            <strong>
              {{if $curr_op->_ref_chir->isPraticien()}}Dr{{/if}} {{$curr_op->_ref_chir->_view}} &mdash;
            {{$curr_op->_ref_plageop->date|date_format:"%d %B %Y"}}
            {{if $curr_op->_nb_files_docs}}
              &mdash; {{$curr_op->_nb_files_docs}} Doc.
            {{/if}}
            </strong>
          </td>
        </tr>
        <tbody class="operEffect" id="oper{{$curr_op->operation_id}}">
          <tr>
            <td colspan="2">
              <a href="?m=planningOp&tab=vw_idx_planning&selChir={{$curr_op->_ref_plageop->chir_id}}&date={{$curr_op->_ref_plageop->date}}">
                Voir l'intervention
              </a>
            </td>
          </tr>
          <tr>
            <th>Actes Médicaux :</th>
            <td class="text">
              <ul>
                {{if $curr_op->libelle}}
                <li><em>[{{$curr_op->libelle}}]</em></li>
                {{/if}}
                {{foreach from=$curr_op->_ext_codes_ccam item=curr_code}}
                <li><strong>{{$curr_code->code}}</strong> : {{$curr_code->libelleLong}}</li>
                {{/foreach}}
              </ul>
            </td>
          </tr>
          <tr>
            <th>
              Documents attachés :
            </th>
            <td id="File{{$curr_op->_class}}{{$curr_op->_id}}"></td>
          </tr>
        </tbody>
        {{/foreach}}
        {{/if}}
        {{/foreach}}
      </table>
    </td>
    <td id="vwPatient">
    {{mb_include module=patients template=inc_vw_patient}}
    </td>
  </tr>
  {{/if}}
</table>

