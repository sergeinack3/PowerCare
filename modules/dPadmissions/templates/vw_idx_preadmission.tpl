{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=admissions script=admissions}}
{{mb_script module=compteRendu script=document}}
{{mb_script module=compteRendu script=modele_selector}}
{{mb_script module=files       script=file}}

{{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
  {{mb_script module=appFineClient  script=appFineClient}}
{{/if}}

<script>
  function submitPreAdmission(oForm) {
    return onSubmitFormAjax(oForm, Admissions.updateListPreAdmissions);
  }

  function reloadPreAdmission() {
    Admissions.updateListPreAdmissions();
  }

  {{assign var=auto_refresh_frequency value="dPadmissions automatic_reload auto_refresh_frequency_preadmissions"|gconf}}

  Main.add(function () {
    Admissions.target_date = '{{$date}}';
    Admissions.pre_admission_filter = '{{$filter}}';
    Admissions.pre_admission_sejour_prepared = '{{$sejour_prepared}}';

    // load the first elements
    Admissions.updateSummaryPreAdmissions();
    Admissions.updateListPreAdmissions();

    // start periodical
    {{if $auto_refresh_frequency != 'never'}}
      Admissions.updatePeriodicalSummaryPreAdmissions('{{$auto_refresh_frequency}}');
      Admissions.updatePeriodicalPreAdmissions('{{$auto_refresh_frequency}}');
    {{/if}}

    $("listPreAdmissions").fixedTableHeaders();
    $("allPreAdmissions").fixedTableHeaders();
  });
</script>

<table class="main">
  <tr>
    <td>
      <a href="#legend" onclick="Admissions.showLegend()" class="button search me-tertiary me-dark">Légende</a>
      {{if "astreintes"|module_active}}{{mb_include module=astreintes template=inc_button_astreinte_day date=$date}}{{/if}}
    </td>
    <td style="float: right;">
      <a href="#" onclick="Admissions.printPreAdmission();" class="button print me-tertiary">Imprimer</a>
    </td>
  </tr>
  <tr>
    <td>
      <div id="allPreAdmissions" class="me-align-auto"></div>
    </td>
    <td style="width: 100%">
      <div id="listPreAdmissions" class="me-align-auto"></div>
    </td>
  </tr>
</table>