{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=patient value=$grossesse->_ref_parturiente}}
{{assign var=pere    value=$grossesse->_ref_pere}}
{{assign var=dossier value=$grossesse->_ref_dossier_perinat}}

{{if $print}}
  {{mb_script module=maternite script=dossierMater ajax=1}}
{{/if}}

<script>

  listForms = [
    getForm("Debut-grossesse-{{$grossesse->_guid}}"),
    getForm("Debut-grossesse-{{$dossier->_guid}}")
  ];

  includeForms = function () {
    DossierMater.listForms = listForms.clone();
  };

  submitAllForms = function (callBack) {
    includeForms();
    DossierMater.submitAllForms(callBack);
  };

  Main.add(function () {
    DossierMater.displayGraph('{{$grossesse->_id}}', 'lcc', 'lcc_graph');
    {{if !$print}}
    includeForms();
    DossierMater.prepareAllForms();
    {{/if}}
  });
</script>

{{mb_include module=maternite template=inc_dossier_mater_header id_close="close_dossier_perinat"}}

<table class="main">
  <td class="halfPane">
    <fieldset>
      <legend>Determination du terme</legend>
      <form name="Debut-grossesse-{{$grossesse->_guid}}" method="post"
            onsubmit="return onSubmitFormAjax(this);">
        {{mb_class object=$grossesse}}
        {{mb_key   object=$grossesse}}
        <input type="hidden" name="_count_changes" value="0" />
        <table class="form me-no-box-shadow me-no-align me-small-form">
          <tr>
            <th>{{mb_label object=$grossesse field=rang}}</th>
            <td>{{mb_field object=$grossesse field=rang}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$grossesse field=cycle}}</th>
            <td>{{mb_field object=$grossesse field=cycle}}</td>
          </tr>
          <tr>
            <th class="halfPane">{{mb_label object=$grossesse field=date_dernieres_regles}}</th>
            <td>
              {{mb_field object=$grossesse field=date_dernieres_regles form=Debut-grossesse-`$grossesse->_guid` register=true}}
              <span class="compact">TP {{mb_value object=$grossesse field=_terme_prevu_ddr}}</span>
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$grossesse field=date_debut_grossesse}}</th>
            <td>
              {{mb_field object=$grossesse field=date_debut_grossesse form=Debut-grossesse-`$grossesse->_guid` register=true}}
              <span class="compact">TP {{mb_value object=$grossesse field=_terme_prevu_debut_grossesse}}</span>
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$grossesse field=terme_prevu}}</th>
            <td>{{mb_field object=$grossesse field=terme_prevu form=Debut-grossesse-`$grossesse->_guid` register=true}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$grossesse field=determination_date_grossesse}}</th>
            <td>
              {{mb_field object=$grossesse field=determination_date_grossesse
              style="width: 12em;" emptyLabel="CGrossesse.determination_date_grossesse."}}
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$grossesse field=nb_embryons_debut_grossesse}}</th>
            <td>{{mb_field object=$grossesse field=nb_embryons_debut_grossesse}}</td>
          </tr>
          <tr>
            <th><span class="compact">{{mb_label object=$grossesse field=type_embryons_debut_grossesse}}</span></th>
            <td>
              {{mb_field object=$grossesse field=type_embryons_debut_grossesse
              style="width: 12em;" emptyLabel="CGrossesse.type_embryons_debut_grossesse."}}
            </td>
          </tr>
          <tr>
            <th><span class="compact">{{mb_label object=$grossesse field=rques_embryons_debut_grossesse}}</span></th>
            <td>
              {{if !$print}}
                {{mb_field object=$grossesse field=rques_embryons_debut_grossesse form=Debut-grossesse-`$grossesse->_guid`}}
              {{else}}
                {{mb_value object=$grossesse field=rques_embryons_debut_grossesse}}
              {{/if}}
            </td>
          </tr>
        </table>
      </form>
    </fieldset>
  </td>
  <td rowspan="10" class="text">
    <fieldset>
      <legend>
        Echographies
        <button type="button" class="add not-printable"
                onclick="DossierMater.addEchographie(null, '{{$grossesse->_id}}');">
          {{tr}}Add{{/tr}} {{tr}}CSurvEchoGrossesse.one{{/tr}}
        </button>
      </legend>
      <div id="lcc_graph"></div>
    </fieldset>
  </td>
  </tr>
  <tr>
    <td>
      <form name="Debut-grossesse-{{$dossier->_guid}}" method="post"
            onsubmit="return onSubmitFormAjax(this);">
        {{mb_class object=$dossier}}
        {{mb_key   object=$dossier}}
        <input type="hidden" name="_count_changes" value="0" />
        <input type="hidden" name="grossesse_id" value="{{$grossesse->_id}}" />
        <table class="main layout">
          <tr>
            <td>
              <fieldset>
                <legend>Projet de grossesse</legend>
                <table class="form me-no-box-shadow me-no-align me-small-form">
                  <tr>
                    <th class="halfPane">{{mb_label object=$dossier field=souhait_grossesse}}</th>
                    <td>{{mb_field object=$dossier field=souhait_grossesse default=""}}</td>
                  </tr>
                </table>
              </fieldset>
            </td>
          </tr>
          <tr>
            <td>
              <fieldset>
                <legend>Contraception précédant cette grossesse</legend>
                <table class="form me-no-align me-no-box-shadow me-small-form">
                  <tr>
                    <th class="halfPane">{{mb_label object=$dossier field=contraception_pre_grossesse}}</th>
                    <td>
                      {{mb_field object=$dossier field=contraception_pre_grossesse
                      style="width: 12em;" emptyLabel="CDossierPerinat.contraception_pre_grossesse."}}
                    </td>
                  </tr>
                  <tr>
                    <th><span class="compact">{{mb_label object=$dossier field=grossesse_sous_contraception}}</span></th>
                    <td>
                      {{mb_field object=$dossier field=grossesse_sous_contraception
                      style="width: 12em;" emptyLabel="CDossierPerinat.grossesse_sous_contraception."}}
                    </td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$dossier field=rques_contraception}}</th>
                    <td>
                      {{if !$print}}
                        {{mb_field object=$dossier field=rques_contraception form=Debut-grossesse-`$dossier->_guid`}}
                      {{else}}
                        {{mb_value object=$dossier field=rques_contraception}}
                      {{/if}}
                    </td>
                  </tr>
                </table>
              </fieldset>
            </td>
          </tr>
          <tr>
            <td>
              <script>
                Main.add(function () {
                  Control.Tabs.create('tab-debut_grossesse', true);
                });
              </script>
              <ul id="tab-debut_grossesse" class="control_tabs">
                <li><a href="#grossesse_apres_traitement">Grossesse obtenue après traitement</a></li>
                <li><a href="#prise_medoc_peri_conceptuelle">Prise médicamenteuse en péri-conceptuelle</a></li>
              </ul>

              <div id="grossesse_apres_traitement" class="me-padding-2" style="display: none;">
                <table class="form me-no-box-shadow me-no-align me-small-form">
                  <tr>
                    <th class="halfPane">{{mb_label object=$dossier field=grossesse_apres_traitement}}</th>
                    <td>{{mb_field object=$dossier field=grossesse_apres_traitement default=""}}</td>
                  </tr>
                  <tr>
                    <th><span class="compact">{{mb_label object=$dossier field=type_traitement_grossesse}}</span></th>
                    <td>
                      {{mb_field object=$dossier field=type_traitement_grossesse
                      style="width: 12em;" emptyLabel="CDossierPerinat.type_traitement_grossesse."}}
                    </td>
                  </tr>
                  <tr>
                    <th><span class="compact">{{mb_label object=$dossier field=origine_ovule}}</span></th>
                    <td>{{mb_field object=$dossier field=origine_ovule}}</td>
                  </tr>
                  <tr>
                    <th><span class="compact">{{mb_label object=$dossier field=origine_sperme}}</span></th>
                    <td>{{mb_field object=$dossier field=origine_sperme}}</td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$dossier field=rques_traitement_grossesse}}</th>
                    <td>
                      {{if !$print}}
                        {{mb_field object=$dossier field=rques_traitement_grossesse form=Debut-grossesse-`$dossier->_guid`}}
                      {{else}}
                        {{mb_value object=$dossier field=rques_traitement_grossesse}}
                      {{/if}}
                    </td>
                  </tr>
                </table>
              </div>

              <div id="prise_medoc_peri_conceptuelle" style="display: none;">
                <table class="form me-no-box-shadow me-no-align me-small-form">
                  <tr>
                    <th class="halfPane">{{mb_label object=$dossier field=traitement_peri_conceptionnelle}}</th>
                    <td>{{mb_field object=$dossier field=traitement_peri_conceptionnelle default=""}}</td>
                  </tr>
                  <tr>
                    <th><span class="compact">{{mb_label object=$dossier field=type_traitement_peri_conceptionnelle}}</span></th>
                    <td>{{mb_field object=$dossier field=type_traitement_peri_conceptionnelle}}</td>
                  </tr>
                  <tr>
                    <th><span class="compact">{{mb_label object=$dossier field=arret_traitement_peri_conceptionnelle}}</span></th>
                    <td>{{mb_field object=$dossier field=arret_traitement_peri_conceptionnelle}}</td>
                  </tr>
                  <tr>
                    <th>{{mb_label object=$dossier field=rques_traitement_peri_conceptionnelle}}</th>
                    <td>{{mb_field object=$dossier field=rques_traitement_peri_conceptionnelle form=Debut-grossesse-`$dossier->_guid`}}</td>
                  </tr>
                </table>
              </div>
            </td>
          </tr>
        </table>
      </form>
    </td>
  </tr>
</table>
