{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=pat_selector ajax=$ajax}}
{{mb_script module=maternite script=placement ajax=$ajax}}

<script>
  PatSelector.initPecPatient = function() {
    this.sForm = 'pecPatiente';
    this.sId   = "patient_id";
    this.sView = "_patient_view";
    this.sSexe = "_patient_sexe";
    this.pop();
  };

  Main.add(function() {
    Placement.initPec('{{$terme_min}}', '{{$terme_max}}');
    Grossesse.show_empty = 0;
    Grossesse.is_edit_consultation = true;
  });
</script>

<form name="pecPatiente" method="post"
      onsubmit="return onSubmitFormAjax(this, {onComplete : function() {
        Placement.refreshNonPlaces();
        Placement.refreshCurrPlacement();
      }});">
  <input type="hidden" name="m"     value="cabinet" />
  <input type="hidden" name="dosql" value="do_consult_now" />
  <input type="hidden" name="_in_suivi" value="1" />
  <input type="hidden" name="_in_maternite" value="1" />
  <input type="hidden" name="sejour_id" value="{{$sejour->_id}}" />
  <input type="hidden" name="_force_create_sejour" />
  <input type="hidden" name="callback" value="Placement.callbackPecPatiente" />

  <div class="me-text-align-center me-margin-top-4" style="text-align: center;">
    {{mb_field object=$sejour field=patient_id hidden=true onchange="Placement.checkParturiente()"}}

    <input type="text" name="_patient_view" style="width: 15em; font-size: 15pt;" placeholder="{{tr}}CPatient|f{{/tr}}" value="{{$consult->_ref_patient->_view}}" />

    {{mb_field object=$consult field=_active_grossesse typeEnum=checkbox onchange="if (\$V(this.form._patient_view)) { Placement.autocomplete_pat.activate(); }"}}
    {{mb_label object=$consult field=_active_grossesse typeEnum=checkbox}}

    <button type="button" class="search notext" onclick="PatSelector.initPecPatient()">{{tr}}Search{{/tr}}</button>

    <button type="button" class="edit notext edit_patient" onclick="Patient.editModal($V(this.form.patient_id));" disabled>{{tr}}Edit{{/tr}}</button>

    <div class="me-margin-top-8" style="font-size: 1.5em;">
        {{assign var=last_grossesse value=$sejour->_ref_patient->_ref_last_grossesse}}
      {{mb_label class=CGrossesse field=terme_prevu}} : <span id="terme_area">{{if $last_grossesse && $last_grossesse->_id}}
            {{mb_value object=$last_grossesse field=terme_prevu}}
      {{else}}&mdash;{{/if}}</span>
      {{mb_include module=maternite template=inc_input_grossesse object=$consult patient=$consult->_ref_patient show_empty=0 large_icon=1}}
    </div>
  </div>

  <table class="main">
    <tr>
      <td style="vertical-align: top;">
        {{mb_include module=maternite template=inc_fieldset_pec_adm}}
      </td>
      <td class="halfPane sejour_part" style="vertical-align: top; {{if !$show_sejour}}display: none;{{/if}}">
        {{mb_include module=maternite template=inc_fieldset_geoloc}}
      </td>
    </tr>

    <tr>
      <td colspan="2" class="button">
        <button class="save">{{tr}}Create{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
