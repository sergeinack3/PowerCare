{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $isImedsInstalled}}
  {{mb_script module="dPImeds" script="Imeds_results_watcher"}}
{{/if}}
{{assign var=imagerie_etendue value="dPurgences CRPU imagerie_etendue"|gconf}}

<script>
  var refreshExecuter;

  function refreshAttente(form, rpu_id) {
    var url = new Url("urgences", "ajax_vw_attente");
    url.addParam("rpu_id"      , rpu_id);
    url.addParam("type_attente", $V(form.type_attente));
    url.addParam("attente", 1);
    url.requestUpdate('retour-'+$V(form.type_attente)+'-'+ rpu_id);
  }

  Main.add(function () {
    Calendar.regField(getForm("changeDate").date, null, {noView: true});

    refreshExecuter = new PeriodicalExecuter(function() {
      getForm("changeDate").submit();
    }, 60);

    {{if $isImedsInstalled}}
      ImedsResultsWatcher.loadResults();
    {{/if}}
  });
</script>

<table style="width:100%">
  <tr>
    <th>
     le
     <big>{{$date|date_format:$conf.longdate}}</big>
      <form action="?" name="changeDate" method="get">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="tab" value="{{$tab}}" />
        <input type="hidden" name="date" class="date" value="{{$date}}" onchange="this.form.submit()" />
      </form>
    </th>
  </tr>
</table>

<table class="tbl">
  <tr>
    <th class="title" rowspan="2">{{mb_title class=CRPU field="_patient_id"}}</th>
    <th class="title" rowspan="2">{{mb_title class=CRPU field="_responsable_id"}}</th>
    <th class="title" colspan="2">{{tr}}CRPU-radio{{/tr}}</th>
    <th class="title" colspan="2">{{tr}}CRPU-bio{{/tr}}</th>
    <th class="title" colspan="2">{{tr}}CRPU-specia{{/tr}}</th>
  </tr>
  <tr>
    <th>{{tr}}CRPUAttente-radio-depart{{/tr}}</th>
    <th>{{tr}}CRPUAttente-radio-retour{{/tr}}</th>
    <th>{{tr}}CRPUAttente-bio-depart{{/tr}}</th>
    <th>{{tr}}CRPUAttente-bio-retour{{/tr}}</th>
    <th>{{tr}}CRPUAttente-specialiste-depart{{/tr}}</th>
    <th>{{tr}}CRPUAttente-specialiste-retour{{/tr}}</th>
  </tr>
  {{foreach from=$listSejours item=_sejour}}
    {{assign var=sejour_id value=$_sejour->_id}}
    {{assign var=rpu value=$_sejour->_ref_rpu}}
    {{assign var=rpu_id value=$rpu->_id}}
    {{assign var=patient value=$_sejour->_ref_patient}}

    <tr style="text-align: center;">
      <td {{if $_sejour->sortie_reelle}}class="opacity-60"{{/if}}>
        <a class="button search notext me-primary" style="float: right"
           href="?m=patients&tab=vw_full_patients&patient_id={{$patient->_id}}&sejour_id={{$_sejour->_id}}">
          {{tr}}Voir{{/tr}}
        </a>
        <a href="#1" onclick="Urgences.pecInf('{{$sejour_id}}', '{{$rpu_id}}')">
          <strong>
          {{$patient}}
          </strong>

          {{mb_include module=patients template=inc_icon_bmr_bhre}}

          <br />{{mb_include module=patients template=inc_vw_ipp ipp=$patient->_IPP}}
        </a>
      </td>

      <td {{if $_sejour->sortie_reelle}}class="opacity-60"{{/if}}>
        <a href="#1" onclick="Urgences.pecInf('{{$sejour_id}}', '{{$rpu_id}}')">
          {{$_sejour->_ref_praticien->_view}}
        </a>
      </td>

      {{if $imagerie_etendue}}
        {{mb_include module=urgences template=inc_vw_attente_imagerie affectations=$_sejour->_ref_affectations sortie=$_sejour->sortie}}
      {{else}}
        {{mb_include module=urgences template=inc_vw_attente type_attente="radio"}}
      {{/if}}

      {{mb_include module=urgences template=inc_vw_attente type_attente="bio"}}

      {{mb_include module=urgences template=inc_vw_attente type_attente="specialiste"}}
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="8" class="empty">{{tr}}CRPUAttente.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
