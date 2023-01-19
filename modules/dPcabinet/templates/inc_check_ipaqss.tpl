{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<style>
  div.rond {
    font-size: 1.3em;
    border-radius: 50%;
    border: 1px solid #888;
    font-weight: bold;
    display: inline-block;
    width: 1.1em;
    height: 1.1em;
    vertical-align: middle;
    text-align: center;
  }
  div.check {
    background-color: #91E106;
  }
  div.uncheck {
    background-color: #FF3F19;
  }
</style>

    <div onmouseover="ObjectTooltip.createDOM(this, 'DetailRank1');" class="rond check">1</div>
    <div onmouseover="ObjectTooltip.createDOM(this, 'DetailRank2');" class="rond check">2</div>
    {{assign var="result" value=false}}
    {{if $dm_patient->absence_traitement || $dm_patient->_ref_traitements|@count ||
      ($dm_patient->_ref_prescription && $dm_patient->_ref_prescription->_ref_prescription_lines|@count)}}
      {{assign var="result" value=true}}
    {{/if}}
    <div onmouseover="ObjectTooltip.createDOM(this, 'DetailRank4');" class="rond {{if $result}}check{{else}}uncheck{{/if}}">4</div>

    {{assign var="dm_sejour" value=false}}
    {{if $consult_anesth->operation_id}}
      {{assign var="dm_sejour" value=$consult_anesth->_ref_operation->_ref_sejour->_ref_dossier_medical}}
    {{/if}}
    {{assign var="result" value=false}}
    {{if "dPcabinet CConsultAnesth show_facteurs_risque"|gconf &&
      ((($dm_sejour && $dm_sejour->_id &&
      ($dm_sejour->risque_antibioprophylaxie != "NR" || $dm_sejour->risque_prophylaxie != "NR" ||
      $dm_sejour->risque_MCJ_chirurgie != "NR" || $dm_sejour->risque_thrombo_chirurgie != "NR"))
      || ($dm_patient->_id && ($dm_patient->facteurs_risque != "" ||
      $dm_patient->risque_thrombo_patient != "NR" || $dm_patient->risque_MCJ_patient != "NR"))))}}
      {{assign var="result" value=true}}
    {{/if}}
    <div onmouseover="ObjectTooltip.createDOM(this, 'DetailRank5');"
         class="rond {{if !"dPcabinet CConsultAnesth show_facteurs_risque"|gconf}}orange{{elseif $result}}check{{else}}uncheck{{/if}}">
      5
    </div>
    <div onmouseover="ObjectTooltip.createDOM(this, 'DetailRank6');"
         class="rond {{if $operation->type_anesth}}check{{else}}uncheck{{/if}}">
      6
    </div>
    {{assign var="result" value=false}}
    {{if ($consult_anesth->mallampati && $consult_anesth->bouche && $consult_anesth->distThyro && $consult_anesth->mob_cervicale) || $consult_anesth->conclusion}}
      {{assign var="result" value=true}}
    {{/if}}
    <div onmouseover="ObjectTooltip.createDOM(this, 'DetailRank7');" class="rond {{if $result}}check{{else}}uncheck{{/if}}">7</div>
    {{assign var="result" value=false}}
    {{if $consult_anesth->_ref_consultation->_ref_patient->_ref_constantes_medicales->poids}}
      {{assign var="result" value=true}}
    {{/if}}
    <div onmouseover="ObjectTooltip.createDOM(this, 'DetailRankPoids');"
         class="rond {{if $consult_anesth->_ref_consultation->_ref_patient->_ref_constantes_medicales->poids}}check{{else}}uncheck{{/if}}">P</div>
    <div onmouseover="ObjectTooltip.createDOM(this, 'DetailRankASA');" class="rond {{if $operation->ASA}}check{{else}}uncheck{{/if}}">A</div>
{{mb_include module=cabinet template=vw_legend_check_anesth}}