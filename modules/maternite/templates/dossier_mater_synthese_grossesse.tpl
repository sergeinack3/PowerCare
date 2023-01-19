{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=patient value=$grossesse->_ref_parturiente}}
{{assign var=pere    value=$grossesse->_ref_pere}}
{{assign var=sejour  value=$grossesse->_ref_last_sejour}}
{{assign var=dossier value=$grossesse->_ref_dossier_perinat}}

<script>
  listForms = [
    getForm("SyntheseGrossesse-{{$dossier->_guid}}")
  ];

  includeForms = function () {
    DossierMater.listForms = listForms.clone();
  };

  submitAllForms = function (callBack) {
    includeForms();
    DossierMater.submitAllForms(callBack);
  };

  Main.add(function () {
    {{if !$print}}
    includeForms();
    DossierMater.prepareAllForms();
    {{/if}}
  });
</script>

{{mb_include module=maternite template=inc_dossier_mater_header}}

<script>
  Main.add(function () {
    Control.Tabs.create('tab-synthese_grossesse', true, {foldable: true {{if $print}}, unfolded: true{{/if}}});
    Control.Tabs.create('tab-pathologies_grossesse', true, {foldable: true {{if $print}}, unfolded: true{{/if}}});
  });
</script>

<form name="SyntheseGrossesse-{{$dossier->_guid}}" method="post"
      onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$dossier}}
  {{mb_key   object=$dossier}}
  <input type="hidden" name="_count_changes" value="0" />


  <table class="form me-no-box-shadow me-no-align me-no-bg me-small-form">
    <tr>
      <th class="halfPane">
        {{mb_label object=$dossier field=date_validation_synthese}}
      </th>
      <td>
        {{mb_field object=$dossier field=date_validation_synthese
        form=SyntheseGrossesse-`$dossier->_guid` register=true class="notNull"}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$dossier field=validateur_synthese_id}}</th>
      <td>
        {{mb_field object=$dossier field=validateur_synthese_id style="width: 12em;" class="notNull"
        options=$listConsultants}}
      </td>
    </tr>
  </table>

  <ul id="tab-synthese_grossesse" class="control_tabs">
    <li><a href="#surveillance_grossesse">Surveillance de la grossessse</a></li>
    <li><a href="#consommation_produits_toxiques">Consommation de produits toxiques</a></li>
    <li><a href="#contexte_psycho_social">Contexte psycho-social</a></li>
    <li><a href="#examen_pendant_grossesse">Examens pendant la grossesse</a></li>
    <li><a href="#immunulogie_serodiagnostics">Immunisation - sérodiagnostics</a></li>
    <li><a href="#pathologies_grossesse">Pathologies de la grossesse</a></li>
  </ul>

  <div id="surveillance_grossesse" style="display: none;">
    {{mb_include module=maternite template=inc_dossier_mater_synthese_surveillance}}
  </div>

  <div id="consommation_produits_toxiques" style="display: none;">
    {{mb_include module=maternite template=inc_dossier_mater_synthese_toxique}}
  </div>

  <div id="contexte_psycho_social" style="display: none;">
    {{mb_include module=maternite template=inc_dossier_mater_synthese_psychosocial}}
  </div>

  <div id="examen_pendant_grossesse" style="display: none;">
    {{mb_include module=maternite template=inc_dossier_mater_synthese_examens}}
  </div>

  <div id="immunulogie_serodiagnostics" style="display: none;">
    {{mb_include module=maternite template=inc_dossier_mater_synthese_immuno}}
  </div>

  <div id="pathologies_grossesse" class="me-padding-2" style="display: none;">
    {{mb_include module=maternite template=inc_dossier_mater_synthese_pathologies}}
  </div>
</form>