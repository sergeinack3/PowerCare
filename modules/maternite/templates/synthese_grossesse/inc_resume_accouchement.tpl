{{*
 * @package Mediboard\maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="me-w100" style="font-size: 100%;">
  <tr>
    <th class="category" colspan="2">{{tr}}CAccouchement{{/tr}}</th>
  </tr>
  <tr>
    <th style="width: 6%;">{{mb_label object=$dossier_perinatal field=rques_conduite_a_tenir}}</th>
    <td>
        {{if $dossier_perinatal->rques_conduite_a_tenir}}
            {{mb_value object=$dossier_perinatal field=rques_conduite_a_tenir}}
        {{else}}
            {{tr}}CAccouchement.cesar_motif.{{/tr}}
        {{/if}}
    </td>
  </tr>
  <tr>
    <th>{{mb_label object=$dossier_perinatal field=projet_allaitement_maternel}}</th>
    <td>{{mb_value object=$dossier_perinatal field=projet_allaitement_maternel}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$dossier_perinatal field=projet_analgesie_peridurale}}</th>
    <td>{{mb_value object=$dossier_perinatal field=projet_analgesie_peridurale}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$dossier_perinatal field=facteur_risque}}</th>
    <td>
        {{if $dossier_perinatal->facteur_risque}}
            {{mb_value object=$dossier_perinatal field=facteur_risque}}
        {{else}}
            {{tr}}CAccouchement.cesar_motif.{{/tr}}
        {{/if}}
    </td>
  </tr>
</table>
