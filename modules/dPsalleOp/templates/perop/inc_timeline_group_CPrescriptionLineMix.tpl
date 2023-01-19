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
    <div>
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

      {{mb_include module=prescription template=inc_vw_info_line_perop line=$_line}}
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

          <hr style="width: 70%; border-color: #aaa; margin: 2px auto;">
          <div class="me-text-align-right">
            <div class="me-text-align-right">
              {{if $line->signature_prat}}
                {{me_img_title src="tick.png" icon="tick" class="me-success" style=""}}
                {{tr var1=$line->_ref_praticien}}CPrescription-Signed by the %s{{/tr}} {{if $line->date_signature}}{{tr var1=$line->date_signature|date_format:$conf.date var2=$line->date_signature|date_format:$conf.time}}common-the %s at %s{{/tr}}{{/if}}
                {{/me_img_title}}
              {{/if}}
              <img src="{{$icon}}" style="clear: left;"/>
            </div>
            {{if !$readonly}}
              {{if $can_adm}}
                <button type="button" class="edit notext compact not-printable me-tertiary me-dark"
                        style="min-width: 15px!important;"
                        onclick="SurveillancePerop.viewTimingPerf(null, '{{$line->_id}}', this.up('.surveillance-timeline-container'));">
                  {{tr}}CMediusers_administer{{/tr}}
                </button>
              {{/if}}

              {{assign var=ask_password value=0}}

              {{if !$app->_ref_user->isPraticien() && $line->signature_prat}}
                {{assign var=ask_password value=1}}
              {{/if}}

              <button class="trash notext compact not-printable me-tertiary me-dark"
                      style="min-width: 15px!important;"
                      onclick="var form = getForm('trash-{{$line->_guid}}');
                        var callback = (function() {
                      {{if $ask_password}}
                        Control.Modal.close();
                      {{/if}}
                        this.onsubmit();
                        }).bind(form);

                      {{if $ask_password}}
                        Prescription.askPasswordAction('trash-{{$line->_guid}}', callback, '{{$line->praticien_id}}');
                      {{else}}
                        callback();
                      {{/if}}">{{tr}}Delete{{/tr}}
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
  {{if !$line->sans_planif}}
    <hr style="width: 70%; border-color: #aaa; margin: 2px auto;">
  {{/if}}

  <div style="white-space: nowrap; position:relative; min-height: 50px;">
  {{if $debit_actual_perf && $line->_last_variation && !$line->date_arret}}
    {{tr}}CPrescriptionLineMix-Actual debit{{/tr}}: <strong>{{mb_include module=prescription template=inc_actual_debit line_mix=$line}}</strong> <br />
  {{/if}}

    {{if !$readonly}}
      <div class="me-inline-block" style="position: absolute; right: 0; bottom: 0;">
        <div class="me-text-align-right">
          {{if $line->signature_prat}}
            {{me_img_title src="tick.png" icon="tick" class="me-success" style=""}}
              {{tr var1=$line->_ref_praticien}}CPrescription-Signed by the %s{{/tr}} {{if $line->date_signature}}{{tr var1=$line->date_signature|date_format:$conf.date var2=$line->date_signature|date_format:$conf.time}}common-the %s at %s{{/tr}}{{/if}}
            {{/me_img_title}}
          {{/if}}
          <img src="{{$icon}}" style="clear: left;"/>
        </div>
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

        {{assign var=ask_password value=0}}

        {{if !$app->_ref_user->isPraticien() && $line->signature_prat}}
          {{assign var=ask_password value=1}}
        {{/if}}

        <button class="trash notext compact not-printable me-tertiary me-dark"
                style="min-width: 15px!important;"
                onclick="var form = getForm('trash-{{$line->_guid}}');
                  var callback = (function() {
                {{if $ask_password}}
                  Control.Modal.close();
                {{/if}}
                  this.onsubmit();
                  }).bind(form);

                {{if $ask_password}}
                  Prescription.askPasswordAction('trash-{{$line->_guid}}', callback, '{{$line->praticien_id}}');
                {{else}}
                  callback();
                {{/if}}">{{tr}}Delete{{/tr}}
        </button>
      </div>
    {{/if}}

    {{if $line->ponctual}}
      <span class="texticon texticon-hopital" style="font-weight: bold; font-size:1em; border: none;">
        {{tr}}CPrescriptionLineMix-ponctual{{/tr}}
      </span>
      <br />
    {{/if}}

  {{if $line->_ref_prises|@count}}
    {{if $line->duree_passage}}
      En {{mb_value object=$line field=duree_passage}} {{if $line->unite_duree_passage == "minute"}}min{{else}}h{{/if}}
    {{/if}}
    {{foreach from=$line->_ref_prises item=_prise name=prises}}
      {{$_prise->_ref_moment->_view}}{{if !$smarty.foreach.prises.last}}, {{/if}}
    {{/foreach}}
  {{elseif $line->_frequence}}
    {{if $line->sans_planif}}
      A passer
    {{elseif $line->type_line == "perfusion" || $line->type_line == "aerosol"}}
      {{if $line->continuite === "continue"}}
        {{tr}}CPrescriptionLineMix-Initial outflow{{/tr}}
      {{else}}
        {{tr}}CPrescriptionLineMix-Frequence{{/tr}}
      {{/if}}
    {{/if}}

    <span style="color:black;">
      <strong>{{$line->_frequence}}</strong>
    </span>

    {{if $line->volume_debit && $line->duree_debit && $line->type_line != "oxygene"}}
      ({{mb_value object=$line field=volume_debit}} ml en {{mb_value object=$line field=duree_debit}} h)
    {{/if}}

    {{if $line->continuite === "discontinue" && $line->_frequence_discontinue}}
      <br />
      {{tr}}CPrescriptionLineMix-_frequence_discontinue{{/tr}}: {{$line->_frequence_discontinue}} ml/h
    {{/if}}

      <br />
      <span class="timeline-total texticon texticon-generique" style="margin-left: 1px;"
            onmouseover="ObjectTooltip.createDOM(this, 'detail_total_cumul_{{$line_id}}');">
        {{tr}}CPrescriptionLineMix-_quantite_totale{{/tr}} :

        {{math assign=total equation="x+y" x=$total_cumul.volume_administre.$line_id|round:3 y=$total_cumul.mix.total_cumul.$line_id}}

        {{$total}} ml
      </span>
  {{elseif $line->ponctual && $line->duree_passage}}
      A passer en {{mb_value object=$line field=duree_passage}} {{if $line->unite_duree_passage == "minute"}}min{{else}}h{{/if}}
  {{else}}
    <span class="timeline-total texticon texticon-generique" style="margin-left: 1px;">
      {{tr}}CPrescriptionLineMix-_quantite_totale{{/tr}} : {{$line->_quantite_totale|round:3}} ml
    </span>
  {{/if}}

    {{foreach from=$total_cumul_massique key=_mix_item_id item=_total_cumul_massique}}
      <br />
      <span class="timeline-total-massique texticon" onmouseover="ObjectTooltip.createEx(this, 'CPrescriptionLineMixItem-{{$_mix_item_id}}');">
        {{if $total_cumul_massique|@count > 1}}
          {{$_total_cumul_massique.ucd_view|spancate:20}} :
        {{/if}}

        {{$_total_cumul_massique.qte}} {{$_total_cumul_massique.unit}}
      </span>
    {{/foreach}}

    {{assign var=counter_adm value=$total_cumul.counter.$line_id}}

      <div id="detail_total_cumul_{{$line_id}}" style="display: none;">
        <table class="main tbl">
          <tr>
            <th class="title" colspan="4">{{tr}}CPrescriptionLineMix-Base debit{{/tr}}</th>
          </tr>
          <tr>
            <td colspan="4">
              {{if ($total_cumul.volume_administre.$line_id > 0)}}
                {{$total_cumul.volume_administre.$line_id|round:3}} ml
              {{else}}
                0 {{$total_quantity_administration_unit}}
              {{/if}}
            </td>
          </tr>
          <tr>
            <th class="title" colspan="4">
              {{tr}}CPrescriptionLineMedicament-Total quantity administered bolus{{/tr}} &ndash; {{tr var1=$total_cumul.counter.$line_id}}CPrescriptionLineMedicament-%s seizure{{/tr}}
            </th>
          </tr>
          <tr>
            <th>{{tr}}CAdministration-object_id{{/tr}}</th>
            <th>{{tr}}common-Date{{/tr}}</th>
            <th>{{tr}}CAdministration-quantite{{/tr}}</th>
            <th>{{tr}}CAdministration-administrateur_id{{/tr}}</th>
          </tr>
          {{foreach from=$total_cumul.adms.$line_id item=_lines_mix_item name=administrations_list}}
            {{foreach from=$_lines_mix_item item=_line_mix_item}}
              {{if !$_line_mix_item|instanceof:'Ox\Mediboard\Mpm\CPrescriptionLineMixVariation'}}
                <tr>
                  <td class="text" style="width: 40%;">
                    <span onmouseover="ObjectTooltip.createEx(this, '{{$_line_mix_item->_guid}}');">
                      {{$_line_mix_item->_ref_object->_view}}
                    </span>
                  </td>
                  <td>{{$_line_mix_item->dateTime|date_format:$conf.datetime}}</td>
                  <td>{{$_line_mix_item->quantite}} {{$_line_mix_item->_ref_object->_unite_reference_libelle}}</td>
                  <td>
                    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_line_mix_item->_ref_administrateur}}
                  </td>
                </tr>
              {{/if}}
            {{/foreach}}
          {{/foreach}}
          <tr>
            <td colspan="4">
              {{tr}}CAdministration-Total cumulation{{/tr}} {{if $line->continuite == "continue"}}bolus{{/if}} :
              <span  style="color: black;">
                <strong>{{$total_cumul.mix.$line_id}}</strong>
              </span>

              {{if isset($total_cumul.mix.cumul_massique.$line_id|smarty:nodefaults)}}
                {{foreach from=$total_cumul.mix.cumul_massique.$line_id item=_cumul_mix_item}}
                    <div>
                      {{$_cumul_mix_item.ucd_view}} : <strong>{{$_cumul_mix_item.qte}} {{$_cumul_mix_item.unite}}</strong>
                    </div>
                {{/foreach}}
              {{/if}}
            </td>
          </tr>
          <tr>
            <th class="title" colspan="4">{{tr}}CPrescriptionLineMix-_quantite_totale{{/tr}}</th>
          </tr>
          <tr>
            <td colspan="4">
              {{math assign=total equation="x+y" x=$total_cumul.volume_administre.$line_id|round:3 y=$total_cumul.mix.total_cumul.$line_id}}

              {{$total}} ml
            </td>
          </tr>
        </table>
      </div>
  </div>
{{/if}}
