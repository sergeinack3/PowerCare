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
    getForm("GrossesseActive-{{$grossesse->_guid}}"),
    getForm("Cloture-{{$dossier->_guid}}"),
    getForm("Cloturesup22-{{$dossier->_guid}}"),
    getForm("Clotureinf22-{{$dossier->_guid}}"),
    getForm("AvortementSpontane-{{$grossesse->_guid}}")
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
    <td colspan="2">
      <form name="GrossesseActive-{{$grossesse->_guid}}" method="post"
            onsubmit="return onSubmitFormAjax(this);">
        {{mb_class object=$grossesse}}
        {{mb_key   object=$grossesse}}
        <input type="hidden" name="_count_changes" value="0" />
        <table class="form me-no-align me-no-box-shadow me-no-bg">
          <tr>
            <th colspan="2" style="font-size: 150%;" class="halfPane">{{mb_label object=$grossesse field=active}}</th>
            <td colspan="2" style="font-size: 150%;">{{mb_field object=$grossesse field=active}}</td>
          </tr>
        </table>
      </form>
    </td>
  <tr>
    <td colspan="2">
      <form name="Cloture-{{$dossier->_guid}}" method="post"
            onsubmit="return onSubmitFormAjax(this);">
        {{mb_class object=$dossier}}
        {{mb_key   object=$dossier}}
        <input type="hidden" name="_count_changes" value="0" />
        <input type="hidden" name="grossesse_id" value="{{$grossesse->_id}}" />
        <fieldset>
          <legend>
            {{tr}}CDossierPerinat-type_terminaison_grossesse-desc{{/tr}}
          </legend>
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <th colspan="2" class="halfPane">{{mb_label object=$dossier field=type_terminaison_grossesse}}</th>
              <td colspan="2">
                {{mb_field object=$dossier field=type_terminaison_grossesse
                style="width: 20em;" emptyLabel="CGrossesse.type_terminaison_grossesse."}}
              </td>
            </tr>
          </table>
        </fieldset>
      </form>
    </td>
  </tr>
  <tr>
    <td class="halfPane">
      <form name="Cloturesup22-{{$dossier->_guid}}" method="post"
            onsubmit="return onSubmitFormAjax(this);">
        {{mb_class object=$dossier}}
        {{mb_key   object=$dossier}}
        <input type="hidden" name="_count_changes" value="0" />
        <input type="hidden" name="grossesse_id" value="{{$grossesse->_id}}" />
        <fieldset>
          <legend>{{tr}}CDossierPerinat-type_term_hors_etab-desc{{/tr}}</legend>
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <th class="halfPane">{{mb_label object=$dossier field=type_term_hors_etab}}</th>
              <td>
                {{mb_field object=$dossier field=type_term_hors_etab
                style="width: 20em;" emptyLabel="CGrossesse.type_term_hors_etab."}}
              </td>
            </tr>
          </table>
        </fieldset>
      </form>
    </td>
    <td class="halfPane">
      <form name="Clotureinf22-{{$dossier->_guid}}" method="post"
            onsubmit="return onSubmitFormAjax(this);">
        {{mb_class object=$dossier}}
        {{mb_key   object=$dossier}}
        <input type="hidden" name="_count_changes" value="0" />
        <input type="hidden" name="grossesse_id" value="{{$grossesse->_id}}" />
        <fieldset>
          <legend>{{tr}}CDossierPerinat-type_term_inf_22sa-desc{{/tr}}</legend>
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <th class="quarterPane">{{mb_label object=$dossier field=type_term_inf_22sa}}</th>
              <td class="quarterPane">
                {{mb_field object=$dossier field=type_term_inf_22sa
                style="width: 20em;" emptyLabel="CGrossesse.type_term_inf_22sa."}}
              </td>
            </tr>
          </table>
        </fieldset>
      </form>
    </td>
  </tr>
  <tr>
    <td class="halfPane">
      <form name="AvortementSpontane-{{$grossesse->_guid}}" method="post"
            onsubmit="return onSubmitFormAjax(this);">
        {{mb_class object=$grossesse}}
        {{mb_key   object=$grossesse}}
        <input type="hidden" name="_count_changes" value="0" />
        <fieldset>
          <legend>En cas d'avortement spontané</legend>
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <th class="quarterPane">{{mb_label object=$grossesse field=num_semaines}}</th>
              <td class="quarterPane">
                {{mb_field object=$grossesse field=num_semaines
                style="width: 20em;" emptyLabel="CGrossesse.num_semaines."}}
              </td>
              <td></td>
            </tr>
          </table>
        </fieldset>
      </form>
    </td>
    <td></td>
  </tr>
</table>