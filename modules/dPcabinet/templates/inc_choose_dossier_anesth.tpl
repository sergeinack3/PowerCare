{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=edit_consultation}}
{{mb_default var=mod_ambu value=0}}

<script>
  Main.add(function() {
    var cpa_operation = $('cpa_'+'{{$selOp->_guid}}');
    if (cpa_operation) {
      cpa_operation.show();
    }
  });
</script>

{{assign var=csa_duplicate_by_cabinet value='dPcabinet CConsultation csa_duplicate_by_cabinet'|gconf}}

{{if $selOp->_ref_sejour->_ref_consult_anesth->_id}}
  <script>
    updateOperation = function(operation_id, consult_anesth_id) {
      new Url('cabinet', 'ajax_update_operation')
        .addParam('operation_id', operation_id)
        .addParam('consult_anesth_id', consult_anesth_id)
        .requestModal(800, 250);
    };
  </script>

  {{assign var="consult_anesth" value=$selOp->_ref_sejour->_ref_consult_anesth}}

  <form name="linkConsultAnesth" action="?" method="post">
    <input type="hidden" name="m" value="cabinet" />
    <input type="hidden" name="dosql" value="do_duplicate_dossier_anesth_aed" />
    <input type="hidden" name="_consult_anesth_id" value="{{$consult_anesth->_id}}" />
    <input type="hidden" name="sejour_id" value="{{$selOp->sejour_id}}" />
    <input type="hidden" name="operation_id" value="{{$selOp->_id}}" />
    <input type="hidden" name="redirect" value="0" />
    <table class="form">
      <tr>
        <td class="text">
          <div class="big-info">
            {{assign var=chir_view value=$consult_anesth->_ref_chir->_view}}
            {{if $consult_anesth->_ref_chir->isPraticien()}}
                {{assign var=chir_view value="le Dr `$chir_view`"}}
            {{/if}}
            {{tr var1=$consult_anesth->_date_consult|date_format:$conf.date var2=$chir_view}}CConsultAnesth-Already created in this folder{{/tr}}.
            {{if $consult_anesth->operation_id}}
                {{tr}}CConsultAnesth-Need to duplicate the folder to access{{/tr}}.
            {{else}}
                {{tr}}CConsultAnesth-Need to link the folder to access{{/tr}}.
            {{/if}}
          </div>
        </td>
      </tr>
      <tr>
        <td class="button">
          {{if !$consult_anesth->operation_id}}
            <button type="button" class="submit" onclick="updateOperation('{{$selOp->_id}}', '{{$consult_anesth->_id}}');">
              {{tr}}CConsultAnesth-Link to this operation{{/tr}}
            </button>
          {{else}}
            <button type="button" class="submit" onclick="return onSubmitFormAjax(this.form, function() {document.location.reload();});">
              {{tr}}CConsultAnesth-Duplicate and link{{/tr}}
            </button>
          {{/if}}
        </td>
      </tr>
    </table>
  </form>
  {{mb_return}}
{{/if}}

<script>
  printFiche = function(dossier_anesth_id) {
    var url = new Url("dPcabinet", "print_fiche");
    url.addParam("dossier_anesth_id", dossier_anesth_id);
    url.addParam("print", true);
    url.popup(700, 500, "printFiche");
  };
  Main.add(function(){
    if ($('anesth_tab_group')){
      $('anesth_tab_group').select('a[href=#fiche_anesth]')[0].addClassName('wrong');
    }
  });
</script>

<div class="big-info">
  {{tr var1=$patient->_view}}CConsultAnesth-Information about linking folder{{/tr}}
</div>

<table class="form">
  <tr>
    <th colspan="3" class="category">{{tr}}CConsultAnesth-Link and existing folder{{/tr}}</th>
  </tr>
  {{assign var=dossiers_anesth value=0}}
  {{foreach from=$patient->_ref_consultations item=_consultation}}
    {{if $_consultation->_refs_dossiers_anesth|@count}}
      {{assign var=dossiers_anesth value=1}}
      <tr>
        <th rowspan="{{$_consultation->_refs_dossiers_anesth|@count}}">
          {{tr}}CConsultation{{/tr}}
          {{tr}}date.from{{/tr}} {{$_consultation->_date|date_format:$conf.date}}
        </th>
        {{if $_consultation->annule}}
          <td rowspan="{{$_consultation->_refs_dossiers_anesth|@count}}" colspan="2" class="cancelled">[{{tr}}CConsultation-annule{{/tr}}]</td>
        {{else}}
          {{foreach from=$_consultation->_refs_dossiers_anesth item=_dossier_anesth name=foreach_anesth}}
            {{assign var=chir_anesth value=$_dossier_anesth->_ref_chir}}
            <td class="narrow">
              {{if $chir_anesth->isPraticien()}}Dr{{/if}} {{$chir_anesth->_view}}
            </td>
            <td>
              {{if $_dossier_anesth->_ref_operation->_id}}
                {{tr}}CConsultAnesth-Already linked{{/tr}} :
                <strong>{{$_dossier_anesth->_ref_operation->_view}}</strong>
                <form name="duplicateOpFrm" action="?m={{$m}}" method="post" onsubmit="{{$onSubmit}}">
                  <input type="hidden" name="dosql" value="do_duplicate_dossier_anesth_aed" />
                  <input type="hidden" name="redirect" value="0" />
                  <input type="hidden" name="del" value="0" />
                  <input type="hidden" name="m" value="dPcabinet" />
                  <input type="hidden" name="_consult_anesth_id" value="{{$_dossier_anesth->_id}}" />
                  <input type="hidden" name="operation_id" value="{{$selOp->_id}}" />
                  <button class="link"
                          {{if !array_key_exists($chir_anesth->_id, $listAnesths) && $csa_duplicate_by_cabinet}}disabled{{/if}}
                    >{{tr}}CConsultAnesth-Duplicate and link{{/tr}}</button>
                </form>
              {{elseif $_dossier_anesth->_ref_sejour->_id}}
                {{tr}}CConsultAnesth-Already linked{{/tr}} :
                <strong>{{$_dossier_anesth->_ref_sejour->_view}}</strong>
              {{else}}

                <form name="addOpFrm" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this,
                  function() {
                    if ($('operation_area')) {
                      SalleOp.loadOperation('{{$selOp->_id}}', null, null, 'anesth_tab');
                    }
                    else {
                      refreshFicheAnesth();
                    }
                  });">
                  <input type="hidden" name="dosql" value="do_consult_anesth_aed" />
                  <input type="hidden" name="del" value="0" />
                  <input type="hidden" name="m" value="dPcabinet" />
                  <input type="hidden" name="consultation_anesth_id" value="{{$_dossier_anesth->_id}}" />
                  <input type="hidden" name="operation_id" value="{{$selOp->_id}}" />
                  <button class="tick"
                          {{if !array_key_exists($chir_anesth->_id, $listAnesths) && $csa_duplicate_by_cabinet}}disabled{{/if}}
                    >{{tr}}Associate{{/tr}}</button>
                </form>
              {{/if}}
              <button style="float:right;" type="button" class="print notext" onclick="printFiche('{{$_dossier_anesth->_id}}');"></button>
            </td>
            {{if !$smarty.foreach.foreach_anesth.last}}
              </tr>
              <tr>
            {{/if}}
          {{/foreach}}
        {{/if}}
      </tr>
    {{/if}}
  {{/foreach}}
  {{if !$patient->_ref_consultations|@count || !$dossiers_anesth}}
    <tr>
      <td class="empty"><em>{{tr}}CConsultAnesth-No folder for this patient{{/tr}}</em></td>
    </tr>
    </tr>
  {{/if}}
  {{if $create_dossier_anesth == 1 && $listAnesths|@count}}
    <tr>
      <th colspan="3" class="category">{{tr}}CConsultAnesth-Create new folder{{/tr}}</th>
    </tr>
    <tr>
      <td colspan="3" class="button">
        <form name="createConsult" action="?m={{$m}}" method="post" onsubmit="{{$onSubmit}}">
          <input type="hidden" name="dosql" value="do_consult_now" />
          <input type="hidden" name="m" value="dPcabinet" />
          <input type="hidden" name="del" value="0" />
          <input type="hidden" name="consultation_id" value="" />
          <input type="hidden" name="_operation_id" value="{{$selOp->_id}}" />
          <input type="hidden" name="sejour_id" value="{{$selOp->sejour_id}}" />
          {{if $mod_ambu}}
            <input type="hidden" name="callback" value="Consultation.editModal"/>
          {{/if}}
          <input type="hidden" name="_redirect" value="?" />
          <input type="hidden" name="patient_id" value="{{$selOp->_ref_sejour->patient_id}}" />

          <table class="form">
            <tr>
              <th>{{mb_label class=CConsultation field="_prat_id"}}</th>
              <td>
                <select name="_prat_id">
                  {{foreach from=$listAnesths item=curr_anesth}}
                  <option value="{{$curr_anesth->user_id}}" {{if $selOp->_ref_anesth->user_id == $curr_anesth->user_id}} selected="selected" {{/if}}>
                    {{$curr_anesth->_view}}
                  </option>
                  {{/foreach}}
                </select>
              </td>
            </tr>

            {{mb_include module=cabinet template=inc_ufs_charge_price}}

            <tr>
              <td class="button" colspan="2">
                <button type="submit" class="new">{{tr}}Create{{/tr}}</button>
              </td>
            </tr>
          </table>
        </form>
      </td>
    </tr>
  {{/if}}
</table>
