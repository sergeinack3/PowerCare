{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=patient value=$grossesse->_ref_parturiente}}

{{mb_include module=maternite template=inc_dossier_mater_header with_buttons=0}}

<table class="main">
  <tr>
    <th>
      <button type="button" class="new not-printable"
              onclick="Tdb.editSejour(0, '{{$grossesse->_id}}', '{{$grossesse->parturiente_id}}', DossierMater.refresh);">Nouveau
        séjour
      </button>
    </th>
  </tr>
</table>

<table class="tbl">
  <tr>
    <th colspan="2" class="narrow">Date</th>
    <th>Mode d'entrée</th>
    <th>Motif</th>
    <th>DP</th>
    <th>DA</th>
    <th>Actes</th>
    <th>Sortie</th>
    <th>Mode de sortie</th>
  </tr>
  {{foreach from=$grossesse->_ref_sejours item=sejour}}
    <tr>
      <td>
        <button type="button" class="edit notext not-printable" title="Modifier le séjour"
                onclick="Tdb.editSejour('{{$sejour->_id}}', null, null,  DossierMater.refresh);">
        </button>
      </td>
      <td>
        {{mb_value object=$sejour field=entree}} - {{mb_value object=$sejour field=_sa}} SA
        <br />
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$sejour->_ref_praticien}}
      </td>
      <td>{{mb_value object=$sejour field=mode_entree}}</td>
      <td>{{mb_value object=$sejour field=libelle}}</td>
      <td>{{mb_value object=$sejour field=DP}}</td>
      <td></td>
      <td></td>
      <td>{{mb_value object=$sejour field=sortie}}</td>
      <td>{{mb_value object=$sejour field=mode_sortie}}</td>
    </tr>
  {{/foreach}}
</table>