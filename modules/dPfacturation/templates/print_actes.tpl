{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=reglement}}
{{mb_script module=facturation script=rapport}}

{{if !$ajax}}

<div style="float: right;">
  {{mb_include module=facturation template=inc_totaux_actes}}
</div>

<div>
  <button type="button" class="notext print not-printable" onclick="window.print();">{{tr}}Print{{/tr}}</button>
    {{tr}}Report{{/tr}}
    {{mb_include module=system template=inc_interval_date from=$filter->_date_min to=$filter->_date_max}}
</div>

<!-- Praticiens concernés -->
{{foreach from=$listPrat item=_prat}}
<div>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_prat}}</div>
{{/foreach}}

{{/if}}

{{if $filter->_type_affichage}}
<table class="main">
  {{foreach from=$listPlages item=_sejour key=_date}}
  {{if !$ajax}}
  <tbody id="{{$_date}}">
  {{/if}}

  <tr>
    <td colspan="2">
      <br />
      <br />
      <strong onclick="Rapport.refresh('{{$_date}}')">
        <strong>{{tr}}CSejour-sortie_reelle{{/tr}}: {{$_sejour.plage->sortie|date_format:$conf.longdate}}</strong>
      </strong>
    </td>
  </tr>

  <tr>
    <td colspan="2">
      <table class="tbl">
        <tr>
          <th colspan="2" class="narrow text">{{tr}}CFactureEtablissement{{/tr}}</th>
          <th style="width: 20%;">{{mb_label class=CFactureEtablissement field=patient_id}}</th>
          <th style="width: 20%;">{{tr}}CFactureItem-type{{/tr}}</th>

          {{if $type_aff}}
            <th class="narrow">{{mb_title class=CFactureEtablissement field=_secteur1}}</th>
            <th class="narrow">{{mb_title class=CFactureEtablissement field=_secteur2}}</th>
            <th class="narrow">{{mb_title class=CConsultation field=_somme}}</th>
            <th style="width: 20%;">{{mb_title class=CFactureEtablissement field=du_patient}}</th>
            <th style="width: 20%;">{{mb_title class=CFactureEtablissement field=du_tiers}}</th>
          {{else}}
            <th class="narrow">{{tr}}CFacture-montant{{/tr}}</th>
            <th class="narrow">{{mb_label class=CFactureCabinet field=remise}}</th>
            <th class="narrow">{{mb_title class=CConsultation field=_somme}}</th>
            <th style="width: 20%;">{{mb_title class=CFactureEtablissement field=du_patient}}</th>
          {{/if}}

          <th>{{mb_title class=CFactureCabinet field=patient_date_reglement}}</th>
        </tr>

        {{foreach from=$_sejour.factures item=_facture}}
        <tr>
          {{if $_facture->_id}}
          <td>
            <strong onmouseover="ObjectTooltip.createEx(this, '{{$_facture->_guid}}')">
              {{$_facture}}
              {{if $_facture->_current_fse}}({{tr}}CConsultation-back-fses{{/tr}}: n° {{$_facture->_current_fse_number}}){{/if}}
            </strong>
          </td>
          <td>{{mb_include module=system template=inc_object_notes object=$_facture}}</td>
          {{else}}
          <td colspan="2">
            <strong>{{$_facture}}</strong>
          </td>
          {{/if}}

          <td class="text">
            <a name="{{$_facture->_guid}}">
              {{mb_include module=system template=inc_vw_mbobject object=$_facture->_ref_patient}}
            </a>
          </td>
          <td class="text">
            {{tr}}CSejour{{/tr}} {{tr}}date.from{{/tr}} {{mb_value object=$_sejour.plage field=entree}}
            {{tr}}date.to{{/tr}} {{mb_value object=$_sejour.plage field=sortie}}

            {{foreach from=$_sejour.plage->_ref_operations item=operation}}
              <br/>{{tr}}COperation{{/tr}} {{tr}}date.from{{/tr}} {{mb_value object=$operation field=date}}
              {{if $operation->libelle}}<br /> {{$operation->libelle}}{{/if}}
            {{/foreach}}

            {{foreach from=$_sejour.plage->_ref_consultations item=consult}}
              <br/>{{tr var1=$consult->_datetime|date_format:"%d %B %Y"}}dPcabinet-Consultation of %s{{/tr}}
              {{if $consult->motif}}: {{$consult->motif}}{{/if}}
            {{/foreach}}
          </td>

          <td>{{mb_value object=$_facture field=_secteur1 empty=1}}</td>
          {{if $type_aff}}
            <td>{{mb_value object=$_facture field=_secteur2 empty=1}}</td>
          {{else}}
            <td>{{mb_value object=$_facture field=remise empty=1}}</td>
          {{/if}}
          <td>{{mb_value object=$_facture field=_montant_avec_remise empty=1}}</td>

          <td>
            <table class="layout">
              {{foreach from=$_facture->_ref_reglements_patient item=_reglement}}
              <tr>
                <td class="narrow">
                  <button class="edit notext" type="button" onclick="Rapport.editReglement('{{$_reglement->_id}}', '{{$_date}}');">
                    {{tr}}Edit{{/tr}}
                  </button>
                </td>
                <td class="narrow" style="text-align: right;"><strong>{{mb_value object=$_reglement field=montant}}</strong></td>
                <td>
                  {{mb_value object=$_reglement field=mode}}
                  {{if $_reglement->reference}}({{mb_value object=$_reglement field=reference}}){{/if}}
                </td>
                <td class="narrow">{{mb_value object=$_reglement field=date date=$_sejour.plage->sortie}}</td>
              </tr>
              {{/foreach}}

              {{if abs($_facture->_du_restant_patient) > 0.001}}
              <tr>
                <td colspan="4" class="button">
                  {{assign var=new_reglement value=$_facture->_new_reglement_patient}}
                  <button class="add" type="button" onclick="Rapport.addReglement('{{$_facture->_guid}}', '{{$new_reglement.emetteur}}', '{{$new_reglement.montant}}', '{{$new_reglement.mode}}', '{{$_date}}');">
                    {{if abs($_facture->_du_restant_patient) > 0.01}}
                      {{tr}}Add{{/tr}} <strong>{{$new_reglement.montant}}</strong>
                    {{else}}
                      {{tr}}CReglement-title-create{{/tr}}
                    {{/if}}
                  </button>
                </td>
              </tr>
              {{/if}}
            </table>
          </td>

          {{if $type_aff}}
          <td>
            <table class="layout">
              {{foreach from=$_facture->_ref_reglements_tiers item=_reglement}}
              <tr>
                <td class="narrow">
                  <button class="edit notext" type="button" onclick="Rapport.editReglement('{{$_reglement->_id}}', '{{$_date}}');">
                    {{tr}}Edit{{/tr}}
                  </button>
                </td>
                <td class="narrow" style="text-align: right;"><strong>{{mb_value object=$_reglement field=montant}}</strong></td>
                <td>
                  {{mb_value object=$_reglement field=mode}}
                  {{if $_reglement->reference}}({{mb_value object=$_reglement field=reference}}){{/if}}
                </td>
                <td class="narrow">{{mb_value object=$_reglement field=date date=$_sejour.plage->sortie}}</td>
              </tr>
              {{/foreach}}

              {{if abs($_facture->_du_restant_tiers) > 0.001}}
              <tr>
                <td colspan="4" class="button">
                  {{assign var=new_reglement value=$_facture->_new_reglement_tiers}}
                  <button class="add" type="button" onclick="Rapport.addReglement('{{$_facture->_guid}}', '{{$new_reglement.emetteur}}', '{{$new_reglement.montant}}', '{{$new_reglement.mode}}', '{{$_date}}');">
                    {{if abs($_facture->_du_restant_tiers) > 0.01}}
                      {{tr}}Add{{/tr}} <strong>{{$new_reglement.montant}}</strong>
                    {{else}}
                      {{tr}}CReglement-title-create{{/tr}}
                    {{/if}}
                  </button>
                </td>
              </tr>
              {{/if}}
            </table>
          </td>
          {{/if}}
          <td>
            <form name="edit-date-aquittement-{{$_facture->_guid}}" action="#" method="post">
              {{mb_key object=$_facture}}
              {{mb_class object=$_facture}}
              {{mb_field object=$_facture field=patient_date_reglement form="edit-date-aquittement-`$_facture->_guid`" register=true
                onchange="onSubmitFormAjax(this.form);"}}
            </form>
          </td>
        </tr>

        {{/foreach}}
        <tr>
          <td colspan="4" style="text-align: right" >
            <strong>{{tr}}Total{{/tr}}</strong>
          </td>
          <td><strong>{{$_sejour.total.secteur1|currency}}</strong></td>
          <td><strong>{{$_sejour.total.secteur2|currency}}</strong></td>
          <td><strong>{{$_sejour.total.total|currency}}</strong></td>
          <td><strong>{{$_sejour.total.patient|currency}}</strong></td>
          {{if $type_aff}}
            <td><strong>{{$_sejour.total.tiers|currency}}</strong></td>
          {{/if}}
          <td></td>
        </tr>
      </table>
    </td>
  </tr>

  {{if !$ajax}}
  </tbody>
  {{/if}}
  {{/foreach}}
  {{/if}}

{{if !$ajax}}
</table>
{{/if}}
