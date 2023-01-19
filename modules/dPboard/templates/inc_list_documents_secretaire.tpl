{{*
 * @package Mediboard\Board
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=brouillon value=0}}
{{mb_default var=attente_validation value=0}}
{{mb_default var=a_envoyer value=0}}
{{mb_default var=envoye value=0}}
{{mb_script module=compteRendu script=document ajax=$ajax}}

<table class="tbl me-no-align">
  <tr>
    <th class="title me-text-align-center" colspan="8">
        {{if $brouillon}}
            {{tr}}CStatutCompteRendu.statut.brouillon{{/tr}}/
            {{tr}}CStatutCompteRendu.statut.attente_correction_secretariat{{/tr}}
            ({{if $affichageDocs}}{{$total.a_corriger}}{{else}}0{{/if}})
        {{/if}}
        {{if $attente_validation}}
            {{tr}}CStatutCompteRendu.statut.attente_validation_praticien{{/tr}}
            ({{if $affichageDocs}}{{$total.attente_validation_praticien}}{{else}}0{{/if}})
        {{/if}}
        {{if $a_envoyer}}
            {{tr}}CStatutCompteRendu.statut.a_envoyer{{/tr}}
            ({{if $affichageDocs}}{{$total.a_envoyer}}{{else}}0{{/if}})
        {{/if}}
        {{if $envoye}}
            {{tr}}CStatutCompteRendu.statut.envoye{{/tr}}
            ({{if $affichageDocs}}{{$total.envoye}}{{else}}0{{/if}})
        {{/if}}
    </th>
  </tr>
  <tr>
    <th class="narrow"></th>
    <th>{{tr}}common-Documents{{/tr}}</th>
    <th>{{tr}}common-Patient{{/tr}}</th>
    <th>{{tr}}common-Context{{/tr}}</th>
    {{if $brouillon}}<th>{{tr}}CStatutCompteRendu-statut{{/tr}}</th>{{/if}}
    {{if $brouillon || $attente_validation}}<th>{{tr}}CStatutCompteRendu-commentaire{{/tr}}</th>{{/if}}
    {{if $brouillon || $a_envoyer}}<th>Delai</th>{{/if}}
    {{if $envoye}}<th>{{tr}}CMbFieldSpec.type.date{{/tr}}</th>{{/if}}
    <th title='{{tr}}CStatutCompteRendu-user_id-desc{{/tr}}'>{{tr}}CStatutCompteRendu-user_id{{/tr}}</th>
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
          {{$patient->_view}}
        </span>
            </td>
            <td class="text">
                {{assign var=contexte value=$_cr->_ref_object}}
              <span onmouseover="ObjectTooltip.createEx(this, '{{$contexte->_guid}}')">
                  {{if $contexte->_class == "CConsultation"}}
                      {{tr var1=$contexte->_date|date_format:$conf.date}}CConsultation-Consultation on %s-court{{/tr}}
                  {{elseif $contexte->_class == "COperation"}}
                      {{tr var1=$contexte->date|date_format:$conf.date}}COperation-Intervention on %s-court{{/tr}}
                  {{else}}
                    {{$contexte->_shortview}}
                  {{/if}}
        </span>
            </td>
            {{if $brouillon}}
            <td class="text">
                {{if $_cr->_ref_last_statut_compte_rendu}}
                    {{mb_value object=$_cr->_ref_last_statut_compte_rendu field=statut}}
                {{/if}}
            </td>
            {{/if}}
              {{if $brouillon || $attente_validation}}
                <td class="text">
                    {{if $_cr->_ref_last_statut_compte_rendu}}
                        {{$_cr->_ref_last_statut_compte_rendu->commentaire|wordwrap:30:"<br>\n"}}
                    {{/if}}
                </td>
              {{/if}}
              {{if $brouillon || $a_envoyer}}
                <td>
                    {{if $_cr->_ref_last_statut_compte_rendu}}
                        {{$_cr->_ref_last_statut_compte_rendu->_delai_attente_correction}}
                    {{/if}}
                </td>
              {{/if}}
              {{if $envoye}}
                <td class="text">
                    {{if $_cr->_ref_last_statut_compte_rendu}}
                        {{$_cr->_ref_last_statut_compte_rendu->datetime|date_format:$conf.datetime}}
                    {{/if}}
                </td>
              {{/if}}
            <td class="text">
                {{if $_cr->_ref_last_statut_compte_rendu}}
                  <span onmouseover="ObjectTooltip.createEx(this,'{{$_cr->_ref_last_statut_compte_rendu->_ref_utilisateur->_guid}}')">
              {{$_cr->_ref_last_statut_compte_rendu->_ref_utilisateur->_shortview}}
            </span>
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
