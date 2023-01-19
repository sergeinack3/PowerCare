{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=admissions script=admissions ajax=true}}
{{mb_script module=planningOp script=sejour     ajax=true}}

<table class="form me-no-box-shadow">
  <tr>
    <th class="halfPane">{{mb_label object=$sejour field=praticien_id}}</th>
    <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=`$sejour->_ref_praticien`}}</td>
    <td class="button" rowspan="5" style="vertical-align: middle;">

      <button type="button" class="edit not-printable me-tertiary"
              onclick="Admissions.validerSortie('{{$sejour->_id}}', 1, DossierMater.refreshEntreeSortie.curry('{{$sejour->_id}}', 'infos_sortie'));">
        Sortie
      </button>

      <br />

      <button type="button" class="edit not-printable me-tertiary"
              onclick="Sejour.editModal('{{$sejour->_id}}', 0, 0, DossierMater.refreshEntreeSortie.curry('{{$sejour->_id}}', 'infos_sortie'))">
        DHE
      </button>
    </td>
  </tr>
  <tr>
    <th>{{mb_label object=$sejour field=sortie_prevue}}</th>
    <td>{{mb_value object=$sejour field=sortie_prevue}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$sejour field=confirme}}</th>
    <td>{{mb_value object=$sejour field=confirme}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$sejour field=sortie_reelle}}</th>
    <td>
      {{mb_value object=$sejour field=sortie_reelle}}
      {{if $sejour->sortie_reelle}}({{mb_value object=$sejour field=_duree}} j){{/if}}
    </td>
  </tr>
  <tr>
    <th>{{mb_label object=$sejour field=mode_sortie}}</th>
    <td>{{mb_value object=$sejour field=mode_sortie}}</td>
  </tr>
</table>