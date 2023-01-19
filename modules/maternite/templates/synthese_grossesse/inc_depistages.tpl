{{*
 * @package Mediboard\maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="me-w100" style="font-size: 100%;">
  <tr>
    <th class="category" colspan="2">{{tr}}CDossierPerinat-debut_grossesse-depistages{{/tr}}</th>
  </tr>
  <tr>
    <td class="halfPane">
        {{mb_include module=maternite template=synthese_grossesse/depistages/inc_immuno_hematologie}}
    </td>
    <td class="halfPane">
        {{mb_include module=maternite template=synthese_grossesse/depistages/inc_serologie}}
    </td>
  </tr>
  <tr>
    <td class="halfPane">
        {{mb_include module=maternite template=synthese_grossesse/depistages/inc_biochimie_hemostase}}
    </td>
    <td class="halfPane me-valign-top">
      <table class="me-w100" style="font-size: 100%;">
          {{mb_include module=maternite template=synthese_grossesse/depistages/inc_analyse_urine}}
          {{mb_include module=maternite template=synthese_grossesse/depistages/inc_bilan_vasculo_renal}}
      </table>
    </td>
  </tr>
  <tr>
    <td class="halfPane">
        {{mb_include module=maternite template=synthese_grossesse/depistages/inc_bilan_premier_trimestre}}
    </td>
    <td class="halfPane me-valign-top">
        {{mb_include module=maternite template=synthese_grossesse/depistages/inc_bilan_second_trimestre}}
    </td>
  </tr>
  <tr>
    <td class="halfPane me-valign-top">
        {{mb_include module=maternite template=synthese_grossesse/depistages/inc_depistages_personalises}}
    </td>
    <td class="halfPane me-valign-top">
      <table class="me-w100" style="font-size: 100%;">
          {{mb_include module=maternite template=synthese_grossesse/depistages/inc_depistage_general}}
          {{mb_include module=maternite template=synthese_grossesse/depistages/inc_prelevement_vaginal}}
      </table>
    </td>
  </tr>
</table>
