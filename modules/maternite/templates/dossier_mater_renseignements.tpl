{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=patient      ajax=1}}
{{mb_script module=patients script=pat_selector ajax=1}}

{{assign var=patient value=$grossesse->_ref_parturiente}}
{{assign var=pere    value=$grossesse->_ref_pere}}
{{assign var=sejour  value=$grossesse->_ref_last_sejour}}
{{assign var=dossier value=$grossesse->_ref_dossier_perinat}}

<script>
  listForms = [
    getForm("Mere-{{$patient->_guid}}"),
    getForm("Mere-{{$dossier->_guid}}"),
    getForm("Pere-{{$dossier->_guid}}"),
    getForm("Social-mere-{{$patient->_guid}}"),
    {{if $pere->_id}}
    getForm("Pere-{{$pere->_guid}}"),
    getForm("Social-pere-{{$pere->_guid}}"),
    {{/if}}
    getForm("Social-dossier-{{$dossier->_guid}}"),
    getForm("Psycho-mere-{{$dossier->_guid}}"),
    getForm("Psycho-social-conclusion-{{$dossier->_guid}}"),
    getForm("Tox-mere-{{$dossier->_guid}}"),
    getForm("Tox-pere-{{$dossier->_guid}}"),
    getForm("Tox-dossier-{{$dossier->_guid}}")
  ];

  includeForms = function () {
    DossierMater.listForms = listForms.clone();
  };

  submitAllForms = function (callBack) {
    includeForms();
    DossierMater.submitAllForms(callBack);
  };

  editMere = function () {
    Patient.editModal('{{$patient->_id}}', null, null, DossierMater.refresh);
  };

  editPere = function () {
    Patient.editModal('{{$pere->_id}}', null, null, DossierMater.refresh);
  };

  Main.add(function () {
    {{if !$print}}
    includeForms();
    DossierMater.prepareAllForms();
    {{/if}}

    Control.Tabs.create('tab-renseignements_generaux', true, {foldable: true {{if $print}}, unfolded: true{{/if}}});
  });
</script>

{{mb_include module=maternite template=inc_dossier_mater_header}}

<table class="main">
  <tr>
    <td>
      <fieldset class="me-small">
        <legend>
          Renseignements socio-démographiques
        </legend>
        <table class="main layout">
          <tr>
            <td class="halfPane">
              <table class="form me-margin-bottom-0 me-margin-top-1 me-no-box-shadow">
                <tr>
                  <th class="title me-padding-2 me-line-height-26">
                    Mère
                    <button type="button" class="edit notext not-printable me-tertiary me-float-none" style="float: right;"
                            onclick="submitAllForms(editMere);">
                        {{tr}}Edit{{/tr}}
                    </button>
                  </th>
                </tr>
                <tr>
                  <th class="category me-padding-2 me-text-align-left">{{$patient}} - {{mb_value object=$patient field=_age}}</th>
                </tr>
              </table>
              <table class="main layout me-margin-top-8">
                <tr>
                  <td class="halfPane">
                    {{mb_include module=maternite template=inc_dossier_mater_rens_pat_mere}}
                  </td>
                  <td>
                    {{mb_include module=maternite template=inc_dossier_mater_rens_doss_mere}}
                  </td>
                </tr>
              </table>
            </td>
            <td>
              <script>
                PatSelector.init = function () {
                  this.sForm = "Pere-{{$grossesse->_guid}}";
                  this.sId = "pere_id";
                  this.pop();
                };

                findPere = function () {
                  PatSelector.init();
                };
              </script>
              <form name="Pere-{{$grossesse->_guid}}" method="post"
                    onsubmit="return onSubmitFormAjax(this, DossierMater.refresh);">
                {{mb_class object=$grossesse}}
                {{mb_key   object=$grossesse}}
                {{mb_field hidden=hidden object=$grossesse field=pere_id onchange="this.form.onsubmit()"}}
              </form>
              <table class="form me-margin-bottom-0 me-margin-top-1 me-no-box-shadow">
                <tr>
                  <th class="title me-line-height-26">
                    Père
                    <button type="button" class="search notext not-printable me-tertiary me-float-none" style="float: right;"
                            onclick="submitAllForms(findPere);">
                        {{tr}}Search{{/tr}}
                    </button>
                      {{if $grossesse->pere_id}}
                        <button type="button" class="edit notext not-printable me-float-none me-tertiary" style="float: right;"
                                onclick="submitAllForms(editPere);">
                            {{tr}}Edit{{/tr}}
                        </button>
                      {{/if}}
                  </th>
                </tr>
                {{if $grossesse->pere_id}}
                  <tr>
                    <th class="category me-padding-2 me-text-align-left">{{$pere}} - {{mb_value object=$pere field=_age}}</th>
                  </tr>
                {{/if}}
              </table>
              <table class="main layout">
                <tr>
                  <td class="halfPane">
                    {{mb_include module=maternite template=inc_dossier_mater_rens_pat_pere}}
                  </td>
                  <td>
                    {{mb_include module=maternite template=inc_dossier_mater_rens_doss_pere}}
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </fieldset>
    </td>
  </tr>
</table>

<ul id="tab-renseignements_generaux" class="control_tabs me-margin-top-0">
  <li><a href="#contexte_psycho_social">Contexte psycho-social</a></li>
  <li><a href="#consommation_produits_toxiques">Consommation de produits toxiques</a></li>
</ul>

<div id="contexte_psycho_social" class="me-padding-2" style="display: none;">
  <table class="main layout">
    <tr>
      <td class="halfPane">
        {{mb_include module=maternite template=inc_dossier_mater_rens_social}}
      </td>
      <td>
        {{mb_include module=maternite template=inc_dossier_mater_rens_psycho}}
      </td>
    </tr>
    <tr>
      <td colspan="2">
        {{mb_include module=maternite template=inc_dossier_mater_rens_psycho_soc}}
      </td>
    </tr>
  </table>
</div>

<div id="consommation_produits_toxiques" class="me-padding-2" style="display: none;">
  <table class="main layout">
    <tr>
      <td class="halfPane">
        {{mb_include module=maternite template=inc_dossier_mater_rens_tox_mere}}
      </td>
      <td>
        {{mb_include module=maternite template=inc_dossier_mater_rens_tox_pere}}
        {{mb_include module=maternite template=inc_dossier_mater_rens_tox}}
      </td>
    </tr>
  </table>
</div>