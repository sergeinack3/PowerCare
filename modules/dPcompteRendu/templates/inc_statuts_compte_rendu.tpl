{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <thead>
  <tr>
    <th class="title" colspan="4">{{tr}}CStatutCompteRendu.all{{/tr}}</th>
  </tr>
  <tr>
    <th>{{mb_label class="CStatutCompteRendu" field="statut"}}</th>
    <th>{{mb_label class="CStatutCompteRendu" field="commentaire"}}</th>
    <th>{{mb_label class="CStatutCompteRendu" field="user_id"}}</th>
    <th>{{mb_label class="CStatutCompteRendu" field="datetime"}}</th>
  </tr>
  </thead>
  <tbody>
  {{foreach from=$statuts item=_statut}}
    <tr id="row-{{$_statut->_id}}">
      <td>{{mb_value object=$_statut field=statut}}</td>
      <td>{{mb_value object=$_statut field=commentaire}}</td>
      <td>{{mb_value object=$_statut field=user_id}}</td>
      <td>{{mb_value object=$_statut field=datetime}}</td>
    </tr>
      {{foreachelse}}
    <tr>
      <td class="empty" colspan="4">{{tr}}CStatutCompteRendu.none{{/tr}}</td>
    </tr>
  {{/foreach}}
  </tbody>
</table>
