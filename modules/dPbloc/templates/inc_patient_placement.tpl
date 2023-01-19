{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=with_div value=1}}

{{assign var=sejour  value=$_operation->_ref_sejour}}
{{assign var=salle   value=$_operation->_ref_salle}}
{{assign var=patient value=$sejour->_ref_patient}}

{{if $with_div}}
<div id="placement_{{$_operation->_id}}"
     class="patient draggable"
     data-form_name="{{$_operation->_guid}}_move"
     data-patient_id="{{$_operation->_patient_id}}"
     data-zone_id="{{$_zone->salle_id}}"
     data-bloc_id="{{$salle->bloc_id}}"
     data-last_operation_id="{{$_operation->_id}}" style="padding: 5px;">
  {{/if}}

  <form name="{{$_operation->_guid}}_move" method="post">
    {{mb_key   object=$_operation}}
    {{mb_class object=$_operation}}
    <input type="hidden" name="_bloc_id" />
    <input type="hidden" name="salle_id" value="{{$_operation->salle_id}}" />
    <input type="hidden" name="redirect" value="redirect" /> {{* Faux redirect pour que le callback ci-dessous puisse s'exécuter *}}
    <input type="hidden" name="callback" value="Placement.mapAffectation" />
  </form>

  <div style="float: right;">
    <span style="margin-bottom: 5px; margin-right: 5px;">
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_operation->_ref_praticien initials=border}}
    </span>

    <button type="button" class="hslip notext me-primary" onclick="ChoiceLit.moveServiceOrBlock('{{$sejour->_id}}', 'bloc');"
            style="margin-top: 3px;">{{tr}}CBlocOperatoire-action-Change operating room{{/tr}}
    </button>
  </div>

  <div style="display: inline-block;">
    <span onmouseover="ObjectTooltip.createEx(this, '{{$_operation->_guid}}');">
      <i class="fa fa-female"></i>
      {{$patient}}
    </span>

    {{mb_include module=patients template=inc_icon_bmr_bhre}}

    <div class="compact">
      ({{$patient->_age}})
    </div>
    <div class="compact">
      <span>{{$_operation->libelle}}</span> - {{$_operation->cote}}
    </div>
  </div>

  {{if $with_div}}
</div>
{{/if}}
