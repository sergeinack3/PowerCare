{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=dossier value=$grossesse->_ref_dossier_perinat}}
{{assign var=patient value=$grossesse->_ref_parturiente}}

<script>
  listForms = [
    getForm("Resume-accouchement-{{$dossier->_guid}}"),
    getForm("Resume-accouchement-travail-{{$dossier->_guid}}"),
    getForm("Resume-accouchement-pathologies-{{$dossier->_guid}}"),
    getForm("Resume-accouchement-constantes-{{$dossier->_guid}}"),
    getForm("Resume-accouchement-anomalies-{{$dossier->_guid}}"),
    {{foreach from=$dossier->_ref_accouchements item=_accouchement}}
    getForm("Resume-accouchement-accouchement-{{$_accouchement->_guid}}"),
    {{foreachelse}}
    getForm("Resume-accouchement-accouchement-CAccouchement-none"),
    {{/foreach}}
    getForm("Resume-accouchement-anesthesie-{{$dossier->_guid}}"),
    getForm("Resume-accouchement-delivrance-{{$dossier->_guid}}")
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
<table class="main">
  <tr>
    <td>
      <form name="Resume-accouchement-{{$dossier->_guid}}" method="post" onsubmit="return onSubmitFormAjax(this);">
        {{mb_class object=$dossier}}
        {{mb_key   object=$dossier}}
        <input type="hidden" name="_count_changes" value="0" />
        <input type="hidden" name="grossesse_id" value="{{$grossesse->_id}}" />
        <table class="main layout">
          <tr>
            <td class="halfPane">
              <fieldset class="me-no-box-shadow me-no-bg me-no-align me-padding-0">
                <table class="form me-small-form">
                  <tr>
                    <th class="halfPane">{{mb_label object=$dossier field=lieu_accouchement}}</th>
                    <td>
                      {{mb_field object=$dossier field=lieu_accouchement style="width: 20em;"
                      emptyLabel="CGrossesse.lieu_accouchement."}}
                      <br />
                      {{mb_label object=$dossier field=autre_lieu_accouchement style="display:none"}}
                      {{assign var=placeholder value='Ox\Core\CAppUI::tr'|static_call:"CDossierPerinat-autre_lieu_accouchement"}}
                      {{mb_field object=$dossier field=autre_lieu_accouchement style="width: 20em;" placeholder=$placeholder}}
                    </td>
                  </tr>
                  <tr>
                    <th>{{tr}}CDossierPerinat-accouchement_other_place{{/tr}}</th>
                    <td colspan="2"></td>
                  </tr>
                  <tr>
                    <th><span class="compact">{{mb_label object=$dossier field=nom_maternite_externe}}</span></th>
                    <td colspan="2">{{mb_field object=$dossier field=nom_maternite_externe style="width: 20em;"}}</td>
                  </tr>
                  <tr>
                    <th>{{tr}}CDossierPerinat-Delivrance_other_place{{/tr}}</th>
                    <td colspan="2"></td>
                  </tr>
                  <tr>
                    <th><span class="compact">{{mb_label object=$dossier field=lieu_delivrance}}</span></th>
                    <td colspan="2">{{mb_field object=$dossier field=lieu_delivrance style="width: 20em;"}}</td>
                  </tr>
                </table>
              </fieldset>
            </td>
            <td class="halfPane">
              <fieldset class="me-no-box-shadow me-no-bg me-no-align me-padding-0">
                <table class="form me-small-form">
                  <tr>
                    <th class="halfPane">{{mb_label object=$dossier field=ag_accouchement}}</th>
                    <td>
                      {{mb_field object=$dossier field=ag_accouchement}} sem
                      {{mb_field object=$dossier field=ag_jours_accouchement}} j
                    </td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$dossier field=mode_debut_travail}}</th>
                    <td>
                      {{mb_field object=$dossier field=mode_debut_travail
                      style="width: 20em;" emptyLabel="CGrossesse.mode_debut_travail."}}
                    </td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$dossier field=rques_debut_travail}}</th>
                    <td>
                      {{if !$print}}
                        {{mb_field object=$dossier field=rques_debut_travail form=Resume-accouchement-`$dossier->_guid`}}
                      {{else}}
                        {{mb_value object=$dossier field=rques_debut_travail}}
                      {{/if}}
                    </td>
                  </tr>
                </table>
              </fieldset>
            </td>
          </tr>
        </table>
      </form>
    </td>
  </tr>
  <tr>
    <td>
      <script>
        Main.add(function () {
          Control.Tabs.create('tab-resume', true, {foldable: true {{if $print}}, unfolded: true{{/if}}});
        });
      </script>

      <ul id="tab-resume" class="control_tabs">
        <li><a href="#resumeTravail">{{tr}}CDossierPerinat-Travail{{/tr}}</a></li>
        <li><a href="#resumePathologies">{{tr}}CDossierPerinat-Pathologies{{/tr}}</a></li>
        <li>
          <a href="#resumeAccouchement" {{if !$dossier->_ref_accouchements|@count}}class="empty"{{/if}}>
            {{tr}}CAccouchement{{/tr}} ({{$dossier->_ref_accouchements|@count}})
          </a>
        </li>
        <li><a href="#resumeAnesthesie">{{tr}}CDossierPerinat-Anesthesie{{/tr}}</a></li>
        <li><a href="#resumeDelivrance">{{tr}}CGrossesseAnt-delivrance{{/tr}}</a></li>
      </ul>
      <div id="resumeTravail" style="display: none;">
        {{mb_include module=maternite template=inc_dossier_mater_resume_acc_travail}}
      </div>
      <div id="resumePathologies" style="display: none;">
        {{mb_include module=maternite template=inc_dossier_mater_resume_acc_patho}}
      </div>
      <div id="resumeAccouchement" style="display: none;">
        {{mb_include module=maternite template=inc_dossier_mater_resume_accouchement}}
      </div>
      <div id="resumeAnesthesie" style="display: none;">
        {{mb_include module=maternite template=inc_dossier_mater_resume_acc_anesth}}
      </div>
      <div id="resumeDelivrance" style="display: none;">
        {{mb_include module=maternite template=inc_dossier_mater_resume_acc_delivrance}}
      </div>
    </td>
  </tr>
</table>
