{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=conditionnel_modification value=true}}
{{if $line->conditionnel && !$line->_current_active}}
  {{assign var=conditionnel_modification value=false}}
{{/if}}

<div>
  {{foreach from=$line->_ref_lines item=_line}}
    <div class="me-margin-bottom-8">
      {{if !$line->define_quantity && $_line->_posologie && !$line->sans_planif}}
        <span class="texticon texticon-hopital me-plan-soin-pref-posologie"
              style="font-weight: bold; float: right; font-size: 0.9em; border: none;">{{$_line->_posologie}}</span>
      {{/if}}
      <strong class="text" style="font-size: 0.9em;" onmouseover="ObjectTooltip.createEx(this, '{{$line->_guid}}');"
        {{if !$readonly && $can_adm && $line|instanceof:'Ox\Mediboard\Mpm\CPrescriptionLineMix' && $conditionnel_modification}}
        onclick="SurveillancePerop.viewTimingPerf(null, '{{$line->_id}}', this.up('.surveillance-timeline-container'));"
        {{/if}}>
        {{$_line->_ucd_view}}
      </strong>
    </div>
  {{/foreach}}
</div>

{{if $line->_ref_prises && $line->_ref_prises|@count}}
  <br/>
  {{mb_include module=salleOp template=inc_vw_line_prises_tag}}
{{else}}
  {{foreach from=$line->_ref_lines item=_line}}
    {{if $_line->quantite_debit}}
      - <br/>
      <span style="font-weight: bolder; color: #f00;">
        {{$_line->quantite_debit}} {{"/\(.*\)/"|preg_replace:"":$_line->_libelle_unite_prescription_debit}} / {{if $_line->temps_debit > 1}}{{$_line->temps_debit}}{{/if}}
        {{if $_line->unite_temps_debit == "hour"}}h{{else}}{{$_line->unite_temps_debit}}{{/if}}
      </span>
    {{/if}}
  {{/foreach}}
{{/if}}

{{if $line->conditionnel}}
  <div>
    {{if "dPprescription CPrescription use_line_segment"|gconf}}
      <span class="texticon texticon-cond"
            title="Ligne conditionnelle"
            onclick="Prescription.viewSegments('','{{$line->_guid}}');" style="float: right; cursor: pointer;">
        {{tr}}CPrescriptionLineMedicament-conditionnel-court{{/tr}}
      </span>

      {{if (!$line->duree_activation || !$line->debut_activation) && !$print}}
        {{if !$line->debut_activation || ($line->debut_activation && $line->fin_activation)}}
          <button class="tick compact me-small" type="button" onclick="Prescription.editSegmentLine('', '{{$line->_id}}', '{{$line->_class}}', 'debut', '{{$type}}');">{{tr}}Enable{{/tr}}</button>
        {{else}}
          <button class="cancel compact me-small" type="button" onclick="Prescription.editSegmentLine('', '{{$line->_id}}', '{{$line->_class}}', 'fin', '{{$type}}');">{{tr}}Disable{{/tr}}</button>
        {{/if}}
      {{/if}}

    {{else}}
      {{if !$print}}
        <form action="?" method="post" name="activeCondition-{{$line->_id}}-{{$line->_class}}">
          <input type="hidden" name="m" value="mpm" />
          <input type="hidden" name="dosql" value="do_prescription_line_mix_aed" />
          <input type="hidden" name="{{$line->_spec->key}}" value="{{$line->_id}}" />
          <input type="hidden" name="del" value="0" />

          {{if !$line->_current_active}}
            <!-- Activation -->
            <input type="hidden" name="debut_activation" value="now" />
            <input type="hidden" name="fin_activation" value="" />
            <button class="tick compact" type="button" onclick="onSubmitFormAjax(this.form, function() {
              SurveillancePerop.refreshContainer(null, '{{$type}}');
              });">
              {{tr}}Enable{{/tr}}
            </button>
          {{else}}
            <!-- Activation -->
            <input type="hidden" name="fin_activation" value="now" />
            <button class="cancel compact me-tertiary" type="button" onclick="onSubmitFormAjax(this.form, function() {
              SurveillancePerop.refreshContainer(null, '{{$type}}');
              });">
              {{tr}}Disable{{/tr}}
            </button>
          {{/if}}
        </form>
      {{/if}}
    {{/if}}
  </div>
{{/if}}

{{if !$readonly && $can_adm && (!$line->conditionnel || ($line->conditionnel && $line->_current_active))}}
  <div style="clear: both;">
      {{assign var=mode_perop value=true}}
      {{if $line->continuite === "discontinue"}}
        {{assign var=mode_perop value=false}}
      {{/if}}

      {{mb_include module=planSoins template=inc_actions_perf
        prescription_line_mix=$line
        reload_mode="perop"
        pose_callback="SurveillancePerop.submitPosePerf"
        retrait_callback="SurveillancePerop.submitRetraitPerf"
        display_no_planif_message=false
        mode_perop=true
      }}

    {{if $line->type_line == "oxygene"}}
      <div style="text-align: center;">
          {{mb_include module=planSoins template=inc_pause_retrait_perf
          _prescription_line_mix=$line
          reload_mode="perop"
          pose_callback="SurveillancePerop.viewTimingPerf"
          retrait_callback="SurveillancePerop.viewTimingPerf"
          display_no_planif_message=false
          oxygene_perop=true
          mode_perop=true
          }}

          <div class="me-inline-block me-text-align-right" style="position: absolute;top: 0px;left: 92%;">
              {{if !$readonly && $can_adm}}
                <button type="button" class="edit notext compact not-printable me-tertiary me-dark"
                        style="min-width: 15px!important;"
                        onclick="SurveillancePerop.viewTimingPerf(null, '{{$line->_id}}', this.up('.surveillance-timeline-container'));">
                  {{tr}}CMediusers_administer{{/tr}}
                </button>
              {{/if}}
          </div>
      </div>
    {{/if}}
  </div>
{{/if}}

{{assign var=debit_actual_perf value="planSoins general debit_actual_perf"|gconf}}
{{assign var=line_id           value=$line->_id}}

{{if $line->type_line != "oxygene" && $line->_quantite_totale}}
  <div style="white-space: nowrap; position:relative; min-height: 25px;">
  {{if $debit_actual_perf && $line->_last_variation && !$line->date_arret}}
    {{tr}}CPrescriptionLineMix-Actual debit{{/tr}}: <strong>{{mb_include module=prescription template=inc_actual_debit line_mix=$line}}</strong> <br />
  {{/if}}

    {{if !$readonly}}
      <div class="me-inline-block" style="position: absolute; right: 0; bottom: 0;">
        {{if $can_adm}}
          {{if $line->type_line != "oxygene"}}
            <button type="button" class="injection notext compact not-printable me-tertiary me-dark"
                    style="min-width: 15px!important;"
                    onclick="SurveillancePerop.editPeropAdministration('{{$interv->_id}}', null, '{{$line->_guid}}', '', '{{$type}}');">
              {{tr}}CMediusers_administer{{/tr}}
            </button>
          {{else}}
            <button type="button" class="edit notext compact not-printable me-tertiary me-dark"
                    style="min-width: 15px!important;"
                    onclick="SurveillancePerop.viewTimingPerf(null, '{{$line->_id}}', this.up('.surveillance-timeline-container'));">
              {{tr}}CMediusers_administer{{/tr}}
            </button>
          {{/if}}
        {{/if}}
      </div>
    {{/if}}

    {{if $line->ponctual}}
      <span class="texticon texticon-hopital" style="font-weight: bold; font-size:1em; border: none;">
        {{tr}}CPrescriptionLineMix-ponctual{{/tr}}
      </span>
      <br />
    {{/if}}
  </div>
{{/if}}
