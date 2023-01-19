{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=type_view value=0}}
{{mb_script module=cabinet script=reglement}}
{{mb_script module=facturation script=rapport}}

{{assign var=type_aff value=1}}

{{if !$ajax}}
  <div style="float: right;">
    {{mb_include module=facturation template=inc_totaux_rapport}}
  </div>

  <div>
    <strong>
      <button type="button" class="notext print not-printable" onclick="window.print();">{{tr}}Print{{/tr}}</button>
      {{tr}}Report{{/tr}}
      {{mb_include module=system template=inc_interval_date from=$filter->_date_min to=$filter->_date_max}}
    </strong>
  </div>

  <div>{{tr}}CReglement-considered{{/tr}} :</div>

  {{if $filter->_etat_reglement_patient}}
    <div>
      {{tr}}CReglement-patient|pl{{/tr}}:
      {{tr}}CConsultation._etat_reglement_tiers.{{$filter->_etat_reglement_patient}}{{/tr}}
    </div>
  {{/if}}

  {{if $filter->_etat_reglement_tiers}}
    <div>
      {{tr}}CReglement-tier|pl{{/tr}}:
      {{tr}}CConsultation._etat_reglement_tiers.{{$filter->_etat_reglement_tiers}}{{/tr}}
    </div>
  {{/if}}

  <!-- Praticiens concernés -->
  {{foreach from=$listPrat item=_prat}}
    <div>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_prat}}</div>
  {{/foreach}}

{{/if}}

{{if $filter->_type_affichage}}
<table class="main">
  {{foreach from=$listEvt item=_evt}}
    {{if !$ajax}}
      <tbody id="{{$_evt.evt->_guid}}">
    {{/if}}

    <tr>
      <td colspan="2">
        <br />
        <br />
        <strong>
          {{$_evt.evt->_ref_praticien}}
          &mdash; {{$_evt.evt->date|date_format:$conf.longdate}}
          {{if $_evt.evt->libelle}}
            : {{mb_value object=$_evt.evt field=libelle}}
          {{/if}}
        </strong>
      </td>
    </tr>

    <tr>
      <td colspan="2">
        <table class="tbl">
          <tr>
            <th colspan="2" class="narrow text">{{tr}}CFactureCabinet{{/tr}}</th>
            <th style="width: 20%;">{{mb_label class=CConsultation field=patient_id}}</th>
            <th style="width: 20%;">{{mb_label class=CConsultation field=tarif}}</th>

            {{if $type_aff}}
              <th class="narrow">{{mb_title class=CConsultation field=secteur1}}</th>
              <th class="narrow">{{mb_title class=CConsultation field=secteur2}}</th>
              <th class="narrow">{{mb_title class=CConsultation field=secteur3}}</th>
              <th class="narrow">{{mb_title class=CConsultation field=du_tva}}</th>
              <th class="narrow">{{mb_title class=CConsultation field=_somme}}</th>
              <th style="width: 20%;">{{mb_title class=CConsultation field=du_patient}}</th>
              <th style="width: 20%;">{{mb_title class=CConsultation field=du_tiers}}</th>
            {{else}}
              <th class="narrow">{{tr}}CFacture-montant{{/tr}}</th>
              <th class="narrow">{{mb_label class=CFactureCabinet field=remise}}</th>
              <th class="narrow">{{mb_title class=CConsultation field=_somme}}</th>
              <th style="width: 20%;">{{mb_title class=CConsultation field=du_patient}}</th>
            {{/if}}

            <th>{{mb_title class=CFactureCabinet field=patient_date_reglement}}</th>
          </tr>

          {{foreach from=$_evt.factures item=_facture}}
            <tr id="line_facture_{{$_facture->_guid}}">
              {{if $_facture->_id}}
                <td>
                  <strong onmouseover="ObjectTooltip.createEx(this, '{{$_facture->_guid}}')">
                    {{$_facture}}
                  </strong>
                  {{if $_facture->group_id != $g}}
                    <span class="compact"><br />({{$_facture->_ref_group}})</span>
                  {{/if}}
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
                <div {{if !$_evt.evt->tarif}} class="empty" {{/if}}>
                  <span onmouseover="ObjectTooltip.createEx(this, '{{$_evt.evt->_guid}}')">
                    {{mb_value object=$_evt.evt field=tarif default=None}}
                  </span>
                </div>
              </td>

              <td>{{mb_value object=$_facture field=_secteur1 empty=1}}</td>
              {{if $type_aff}}
                <td>{{mb_value object=$_facture field=_secteur2 empty=1}}</td>
                <td>{{mb_value object=$_facture field=_secteur3 empty=1}}</td>
                <td>{{mb_value object=$_facture field=du_tva empty=1}}</td>
              {{else}}
                <td>{{mb_value object=$_facture field=remise empty=1}}</td>
              {{/if}}
              <td>{{mb_value object=$_facture field=_montant_avec_remise empty=1}}</td>

              <td>
                <table class="layout">
                  {{foreach from=$_facture->_ref_reglements_patient item=_reglement}}
                    <tr>
                      <td class="narrow">
                        <button class="edit notext" type="button" onclick="Rapport.editReglement('{{$_reglement->_id}}', '{{$_reglement->date}}', '{{$_facture->_guid}}', '{{$_evt.evt->_guid}}');">
                          {{tr}}Edit{{/tr}}
                        </button>
                      </td>
                      <td class="narrow" style="text-align: right;"><strong>{{mb_value object=$_reglement field=montant}}</strong></td>
                      <td>
                        {{mb_value object=$_reglement field=mode}}
                        {{if $_reglement->reference}}({{mb_value object=$_reglement field=reference}}){{/if}}
                      </td>
                      <td class="narrow">{{mb_value object=$_reglement field=date date=$_evt.evt->date}}</td>
                    </tr>
                  {{/foreach}}

                  {{if abs($_facture->_du_restant_patient) > 0.01}}
                    <tr>
                      <td colspan="4" class="button">
                        {{assign var=new_reglement value=$_facture->_new_reglement_patient}}
                        <button class="add" type="button" onclick="Rapport.addReglement('{{$_facture->_guid}}', '{{$new_reglement.emetteur}}', '{{$new_reglement.montant}}', '{{$new_reglement.mode}}', null, '{{$_evt.evt->_guid}}');">
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

              {{if $type_aff}}
                <td>
                  <table class="layout">
                    {{foreach from=$_facture->_ref_reglements_tiers item=_reglement}}
                      <tr>
                        <td class="narrow">
                          <button class="edit notext" type="button" onclick="Rapport.editReglement('{{$_reglement->_id}}', '{{$_reglement->date}}', '{{$_facture->_guid}}', '{{$_evt.evt->_guid}}');">
                            {{tr}}Edit{{/tr}}
                          </button>
                        </td>
                        <td class="narrow" style="text-align: right;"><strong>{{mb_value object=$_reglement field=montant}}</strong></td>
                        <td>
                          {{mb_value object=$_reglement field=mode}}
                          {{if $_reglement->reference}}({{mb_value object=$_reglement field=reference}}){{/if}}
                        </td>
                        <td class="narrow">{{mb_value object=$_reglement field=date date=$_evt.evt->date}}</td>
                      </tr>
                    {{/foreach}}

                    {{if abs($_facture->_du_restant_tiers) > 0.01}}
                      <tr>
                        <td colspan="4" class="button">
                          {{assign var=new_reglement value=$_facture->_new_reglement_tiers}}
                          <button class="add" type="button" onclick="Rapport.addReglement('{{$_facture->_guid}}', '{{$new_reglement.emetteur}}', '{{$new_reglement.montant}}', '{{$new_reglement.mode}}', null, '{{$_evt.evt->_guid}}');">
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
                  {{mb_key    object=$_facture}}
                  {{mb_class  object=$_facture}}
                  {{mb_field object=$_facture field=patient_date_reglement form="edit-date-aquittement-`$_facture->_guid`" register=true
                  onchange="onSubmitFormAjax(this.form);"}}
                </form>
              </td>
            </tr>

          {{/foreach}}
          <tr id="{{$_evt.evt->_guid}}_total">
            <td colspan="4" style="text-align: right" >
              <strong>{{tr}}Total{{/tr}}</strong>
            </td>
            <td><strong>{{$_evt.total.secteur1|currency}}</strong></td>
            <td><strong>{{$_evt.total.secteur2|currency}}</strong></td>
            {{if $type_aff}}
              <td><strong>{{$_evt.total.secteur3|currency}}</strong></td>
              <td><strong>{{$_evt.total.du_tva|currency}}</strong></td>
            {{/if}}
            <td><strong>{{$_evt.total.total|currency}}</strong></td>
            <td><strong>{{$_evt.total.patient|currency}}</strong></td>
            {{if $type_aff}}
              <td><strong>{{$_evt.total.tiers|currency}}</strong></td>
            {{/if}}
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
