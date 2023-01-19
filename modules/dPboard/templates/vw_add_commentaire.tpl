{{*
 * @package Mediboard\Board
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="add_commentaire_statut" method="post" onsubmit="return onSubmitFormAjax(this,function (){
  Control.Modal.close();
  Board.updateDocuments(getForm('editPrefShowAllDocs'));
  })">

    {{mb_class object=$statut}}
    {{mb_key   object=$statut}}
    {{mb_field object=$statut field="compte_rendu_id" hidden=1 value=$statut->compte_rendu_id}}
    {{mb_field object=$statut field="datetime" hidden=1 value=$statut->datetime}}
    {{mb_field object=$statut field="statut" hidden=1 value=$statut->statut}}
    {{mb_field object=$statut field="user_id" hidden=1 value=$statut->user_id}}

  <table id="add_commentaire_statut" class="form">
    <tr><th class="title me-th-new" colspan="2">{{tr}}CStatutCompteRendu-add-commentaire{{/tr}}</th></tr>
    <tr>
      <th class="narrow">{{mb_label object=$statut field="commentaire"}}</th>
      <td>{{mb_field object=$statut field="commentaire"}}</td>
    </tr>
    <tr>
      <td class="button" colspan="2">
            <button class="modify" type="submit">
                {{tr}}Save{{/tr}}
            </button>
      </td>
    </tr>
  </table>
</form>
