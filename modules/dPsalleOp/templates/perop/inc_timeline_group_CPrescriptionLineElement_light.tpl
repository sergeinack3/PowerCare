{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div style="white-space: nowrap; position:relative;">
  <div class="" style="position: absolute; right: 0;">
      {{if !$readonly}}
        <div class="me-text-align-right">
            {{if $can_adm}}
              <button type="button" class="injection notext compact not-printable me-tertiary me-dark"
                      style="min-width: 15px!important;"
                      onclick="SurveillancePerop.editPeropAdministration('{{$interv->_id}}', this.up('.surveillance-timeline-container'), '{{$line->_guid}}', '', '{{$type}}');">
                  {{tr}}CMediusers_administer{{/tr}}
              </button>
            {{/if}}
        </div>
      {{/if}}
  </div>
</div>


<div style="max-width: 85%; min-height: 50px;">
  <strong style="font-size:0.9em;" onmouseover="ObjectTooltip.createEx(this, '{{$line->_guid}}');">{{$view}}</strong>
  {{if "planSoins general show_dci"|gconf && $line->_ucd_view}}
    <span style="font-weight: normal" class="compact">({{$line->_ucd_view}})</span>
  {{/if}}

    {{if $line->_ref_prises|@count}}
      <br/>
      {{mb_include module=salleOp template=inc_vw_line_prises_tag}}
    {{/if}}

    {{if $line->conditionnel}}
      {{if "dPprescription CPrescription use_line_segment"|gconf}}
        <span class="texticon texticon-cond"
              title="Ligne conditionnelle"
              onclick="Prescription.viewSegments('{{$line->_chapitre}}','{{$line->_guid}}', null, null, '{{$type}}');"
              style="cursor: pointer;">
        {{tr}}CPrescriptionLineMedicament-conditionnel-court{{/tr}}
      </span>
        {{if !$print}}
          {{if !$line->duree_activation || !$line->debut_activation}}
            <br/>
            {{if !$line->debut_activation || ($line->debut_activation && $line->fin_activation) || !$line->_ref_segments|@count}}
              <button class="tick compact me-small" type="button"
                      onclick="Prescription.editSegmentLine('{{$line->_chapitre}}', '{{$line->_id}}', '{{$line->_class}}', 'debut', '{{$type}}');">{{tr}}Enable{{/tr}}</button>
            {{else}}
              <button class="cancel compact me-small" type="button"
                      onclick="Prescription.editSegmentLine('{{$line->_chapitre}}', '{{$line->_id}}', '{{$line->_class}}', 'fin', '{{$type}}');">{{tr}}Disable{{/tr}}</button>
            {{/if}}
          {{/if}}
        {{/if}}
      {{else}}
        <form action="?" method="post" name="activeCondition-{{$line_id}}-{{$line_class}}">
          {{mb_class object=$line}}
          {{mb_key   object=$line}}
          <input type="hidden" name="del" value="0"/>

          {{if !$line->_current_active}}
            <!-- Activation -->
            <input type="hidden" name="debut_activation" value="now"/>
            <input type="hidden" name="fin_activation" value=""/>
            <button class="tick compact me-small" type="button" onclick="onSubmitFormAjax(this.form, function() {
              SurveillancePerop.refreshContainer(null, '{{$type}}');
              });">
              {{tr}}Enable{{/tr}}
            </button>
          {{else}}
            <!-- Activation -->
            <input type="hidden" name="fin_activation" value="now"/>
            <button class="cancel compact me-small" type="button" onclick="onSubmitFormAjax(this.form, function() {
              SurveillancePerop.refreshContainer(null, '{{$type}}');
              });">
              {{tr}}Disable{{/tr}}
            </button>
          {{/if}}
        </form>
      {{/if}}

    <br/>

    {{if $display_mode === "token" && !$readonly}}
      <div style="clear: both; text-align: right">
        {{if isset($line->_back.planifications|smarty:nodefaults)}}
          {{foreach from=$line->_back.planifications item=_planif}}
            {{assign var=planif_id value=$_planif->_id}}
            <div style="text-align: center; width: 20px; display: inline-block; margin: 1px; border-radius: 2px;"
                 data-line_guid="{{$line->_guid}}"
                 data-datetime="{{$_planif->dateTime}}"
                 data-planif_id="{{$_planif->_id}}"
                 data-quantite="{{$_planif->getQuantiteAdministrable()}}"
                 class="draggable timeline-draggable {{$planif_color.$planif_id}}"
                 title="Prévu pour {{$_planif->dateTime|date_format:$conf.datetime}}
          {{$_planif->getQuantiteAdministrable()}}">
              {{$_planif->getQuantiteAdministrable()}}
            </div>
          {{/foreach}}
        {{/if}}
      </div>
    {{/if}}
  {{/if}}
</div>
