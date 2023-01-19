{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=with_div value=1}}
{{mb_default var=refresh_etiquette value=false}}

{{assign var=patient value=$_sejour->_ref_patient}}
{{assign var=affectation value=$_sejour->_ref_curr_affectation}}
{{assign var=last_consult value=$_sejour->_ref_last_consult}}

{{assign var=maman value=1}}
{{assign var=bebe  value=0}}

{{assign var=provisoire value=0}}

{{assign var=parent_sejour_id value=""}}

{{if $patient->_annees < 2}}
  {{assign var=maman value=0}}
  {{assign var=bebe  value=1}}

  {{if !$_sejour->_ref_naissance->date_time}}
    {{assign var=provisoire value=1}}
  {{/if}}

  {{assign var=parent_sejour_id value=$_sejour->_ref_naissance->sejour_maman_id}}
{{/if}}

<script>
  Main.add(function () {
    var div = $("placement_{{$_sejour->_id}}");

    {{if !$provisoire}}
    div.removeClassName("hatching");
    {{/if}}
  });
</script>

{{if $with_div && !$refresh_etiquette}}
<div id="placement_{{$_sejour->_id}}"
     style="padding: 2px;"
     class="patient draggable {{if $provisoire}}hatching{{/if}}
            {{if $affectation->parent_affectation_id}}parent_{{$parent_sejour_id}}{{/if}}"
     data-form_name="{{$_sejour->_guid}}_move"
     data-patient-id="{{$_sejour->patient_id}}"
     data-zone_id="{{$_zone->chambre_id}}"
     data-service_id="{{$_sejour->service_id}}"
     data-affectation_id="{{$affectation->_id}}">
  {{/if}}

  <form name="{{$_sejour->_guid}}_move" method="post">
    <input type="hidden" name="m" value="hospi" />
    <input type="hidden" name="dosql" value="do_affectation_split" />
    <input type="hidden" name="_date_split" value="now" />
    <input type="hidden" name="affectation_id" value="{{$affectation->_id}}" />
    <input type="hidden" name="_service_id" />
    <input type="hidden" name="_new_lit_id" />
    <input type="hidden" name="_mod_mater" value="1"/>
    <input type="hidden" name="sejour_id" value="{{$_sejour->_id}}" />
    <input type="hidden" name="entree" value="{{$affectation->entree}}" />
    <input type="hidden" name="sortie" value="{{$affectation->sortie}}" />
    <input type="hidden" name="effectue" value="1" />
    <input type="hidden" name="no_synchro" value="1" />
    <input type="hidden" name="redirect" value="redirect" /> {{* Faux redirect pour que le callback ci-dessous puisse s'exécuter *}}
    <input type="hidden" name="callback" value="Placement.mapAffectation" />
  </form>

  <div style="float: right;">
    <span>
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_sejour->_ref_praticien initials=border}}
    </span>

    <button type="button" class="hslip notext me-primary" onclick="ChoiceLit.moveServiceOrBlock('{{$_sejour->_id}}', 'service');"
            style="margin-top: 3px;">{{tr}}CBlocOperatoire-action-Change operating room{{/tr}}
    </button>
  </div>

  <div style="display: inline-block;">
    {{if $affectation->parent_affectation_id}}
      <span style="font-size: 1.5em">&rarrhk;</span>
    {{/if}}

    <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}');">
      <i class="fa {{if $maman}}fa-female{{else}}fa-child{{/if}}"></i>
      {{$patient}}
    </span>

    {{mb_include module=patients template=inc_icon_bmr_bhre}}

    <div class="compact">
      ({{$patient->_age}})
    </div>
  </div>

  {{if $last_consult->_id}}
    <div onmouseover="ObjectTooltip.createEx(this, '{{$last_consult->_guid}}');">
      {{tr}}CConsultation{{/tr}} {{tr}}date.from{{/tr}} {{$last_consult->_date|date_format:$conf.date}}
    </div>
  {{/if}}

  {{if $with_div && !$refresh_etiquette}}
</div>
{{/if}}
