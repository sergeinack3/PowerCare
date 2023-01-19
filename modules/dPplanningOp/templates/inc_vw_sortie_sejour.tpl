{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl main">
  <tr>
    <th colspan="2" class="title">Sortie</th>
  </tr>
  <tr>
    <th>{{mb_label object=$sejour field="mode_sortie"}}</th>
    <td>{{mb_value object=$sejour field="mode_sortie"}}</td>
  </tr>
  <tr {{if $sejour->mode_sortie != "transfert"}}style="display:none;"{{/if}}>
    <th>{{mb_label object=$sejour field="etablissement_sortie_id"}}</th>
    <td>{{mb_value object=$sejour field="etablissement_sortie_id"}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$sejour field="transport_sortie"}}</th>
    <td>{{mb_value object=$sejour field="transport_sortie"}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$sejour field="rques_transport_sortie"}}</th>
    {{if !$sejour->rques_transport_sortie}}
      <td class="empty">Aucune remarque</td>
    {{else}}
      <td>{{mb_value object=$sejour field="rques_transport_sortie"}}</td>
    {{/if}}
  </tr>
  <tr>
    <th>{{mb_label object=$sejour field="commentaires_sortie"}}</th>
    {{if !$sejour->commentaires_sortie}}
      <td class="empty">Aucun commentaire</td>
    {{else}}
      <td>{{mb_value object=$sejour field="commentaires_sortie"}}</td>
    {{/if}}
  </tr>
</table>