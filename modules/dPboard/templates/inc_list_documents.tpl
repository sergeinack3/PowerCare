{{*
 * @package Mediboard\Board
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.setTabCount('documents', '{{$crs|@count}}');
    let choixPrat = getForm('ChoixPraticien');
    let editPrefFrom = getForm('editPrefShowAllDocs');
    if (choixPrat) {
      $V(editPrefFrom.praticien_id, $V(choixPrat.praticien_id));
      $V(editPrefFrom.function_id, $V(choixPrat.function_id));
    }
    Board.changeSelectStatut($('statut_compte_rendu'),$('div_statut_compte_rendu'), $V(editPrefFrom.view_praticioner), $V(editPrefFrom.view_secretary));
  });
</script>


<form name="editPrefShowAllDocs" id="editPrefShowAllDocs" method="post" onsubmit="onSubmitFormAjax(this,function (){
        Board.updateDocuments(getForm('editPrefShowAllDocs'));
        })">
  <table class="tbl me-no-align me-no-box-shadow">
    <tr class="me-display-flex">
      <td class="me-flex-1">
        <input type="hidden" name="m" value="admin"/>
        <input type="hidden" name="dosql" value="do_preference_aed"/>
        <input type="hidden" name="user_id" value="{{$app->user_id}}"/>
        <input type="hidden" name="praticien_id" value=""/>
        <input type="hidden" name="function_id" value=""/>
        <input type="hidden" name="pref[show_all_docs]"
               value="{{$app->user_prefs.show_all_docs}}"/>
        <input type="hidden" name="pref[select_view]" value="{{$app->user_prefs.select_view}}"/>
        <label>
          <input type="checkbox" {{if $app->user_prefs.show_all_docs}}checked{{/if}} onclick=
          "$V(this.form.elements['pref[show_all_docs]'], this.checked ? 1 : 0);this.form.onsubmit()"/>
          <span>
              {{tr}}common-show-all-document{{/tr}}
          </span>
        </label>
      </td>
      <td class="me-flex-1">
          <div id='div_statut_compte_rendu'>
            {{mb_label class="CStatutCompteRendu" field="statut" }}
            {{mb_field class="CStatutCompteRendu" field="statut" id='statut_compte_rendu' value=$statut emptyLabel=Tous onchange="this.form.onsubmit(this.form)"}}
          </div>
      </td>
      <td class="me-flex-1">

        <label>{{tr}}common-vue-praticien{{/tr}} <input name="view_praticioner" type="checkbox" onchange="Board.setpreference(this.form);this.form.onsubmit();"
                             {{if $view_praticioner}}checked{{/if}}/></label>
        <label>{{tr}}common-vue-secretaire{{/tr}} <input name="view_secretary" type="checkbox" onchange="Board.setpreference(this.form);this.form.onsubmit();"
                               {{if $view_secretary}}checked{{/if}}/></label>
      </td>
    </tr>
  </table>
  </form>

<table class="tbl me-no-align me-no-box-shadow">
  <tr>
    <th class="narrow"></th>
    <th>Document</th>
    <th>Patient</th>
    <th>Contexte</th>
    <th>{{tr}}CStatutCompteRendu-statut{{/tr}}</th>
    <th>{{tr}}CStatutCompteRendu-commentaire{{/tr}}</th>
    <th>{{tr}}CStatutCompteRendu-user_id{{/tr}}</th>
    <th colspan="2" class="narrow">{{tr}}Actions{{/tr}}</th>
  </tr>

  {{foreach from=$affichageDocs item=_chapitre}}
    <tr>
      <th class="section" colspan="9">{{$_chapitre.name}}</th>
    </tr>
    {{foreach from=$_chapitre.items item=_cr}}
    <tr>
      <td>
        <button type="button" class="edit notext" onclick="Document.edit('{{$_cr->_id}}');" title="{{tr}}Edit{{/tr}}"></button>
      </td>
      <td class="text">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_cr->_guid}}')">
        {{mb_value object=$_cr field=nom}}
        </span>
      </td>
      <td class="text">
        {{assign var=patient value=$_cr->_ref_patient}}
        <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">
          {{$patient}}
        </span>
      </td>
      <td class="text">
        {{assign var=contexte value=$_cr->_ref_object}}
        <span onmouseover="ObjectTooltip.createEx(this, '{{$contexte->_guid}}')">
          {{$contexte}}
        </span>
      </td>
      <td class="text">
          {{if $_cr->_ref_last_statut_compte_rendu}}
              {{mb_value object=$_cr->_ref_last_statut_compte_rendu field=statut}}
          {{/if}}
      </td>
      <td class="text">
          {{if $_cr->_ref_last_statut_compte_rendu}}
              {{mb_value object=$_cr->_ref_last_statut_compte_rendu field=commentaire}}
          {{/if}}
      </td>
      <td class="text">
          {{if $_cr->_ref_last_statut_compte_rendu}}
            <span onmouseover="ObjectTooltip.createEx(this,'{{$_cr->_ref_last_statut_compte_rendu->_ref_utilisateur->_guid}}')">
              {{$_cr->_ref_last_statut_compte_rendu->_ref_utilisateur->_view}}
            </span>
          {{/if}}
      </td>
      <td>
        {{if !$_cr->valide}}
            <button class="modify notext" title="{{tr}}dPBoard-msg-ask_correction{{/tr}}" onclick="Board.askCorrection('{{$_cr->_id}}');"></button>
        {{/if}}
      </td>
      <td>
          {{if !$_cr->valide}}
            <form name="actionDoc" method="post" onsubmit="onSubmitFormAjax(this,function (){
              Board.updateDocuments(getForm('editPrefShowAllDocs'));
              })">
              <input type="hidden" name="m" value="compteRendu"/>
              <input type="hidden" name="dosql" value="do_modele_aed"/>
              <input type="hidden" name="valide" value="1"/>
              <input type="hidden" name="compte_rendu_id" value="{{$_cr->_id}}"/>
              <button type="button" class="tick notext" title="{{tr}}dPBoard-msg-validate_document{{/tr}}"
                      onclick="this.form.onsubmit()">
              </button>
            </form>
          {{/if}}
      </td>

    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="9" class="empty">{{tr}}CCompteRendu.none{{/tr}}</td>
    </tr>
    {{/foreach}}
  {{foreachelse}}
  <tr>
    <td colspan="9" class="empty">{{tr}}CCompteRendu.none{{/tr}}</td>
  </tr>
  {{/foreach}}
</table>
