{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl form" style="width: 100%;">
  {{mb_include module=system template=inc_pagination total=$total_libs current=$page step=50 change_page='changePage'}}
  <tr>
    <th colspan="8" class="title">{{tr}}CLibelleOp.all{{/tr}}</th>
  </tr>
  <tr>
    <th style="width:350px;">{{mb_title class= CLibelleOp field=nom}}</th>
    <th class="narrow">{{mb_title class= CLibelleOp field=services}}</th>
    <th class="narrow">{{mb_title class= CLibelleOp field=date_debut}}</th>
    <th class="narrow">{{mb_title class= CLibelleOp field=date_fin}}</th>
    <th>{{mb_title class= CLibelleOp field=mots_cles}}</th>
    <th class="narrow">{{mb_title class= CLibelleOp field=numero}}</th>
    <th class="narrow">{{mb_title class= CLibelleOp field=statut}}</th>
    <th class="narrow">{{mb_title class= CLibelleOp field=version}}</th>
  </tr>

  {{foreach from=$libelles item=libelle}}
    <tr>
      <td class="text"><a href="#" onclick="Libelle.edit('{{$libelle->_id}}');">{{mb_value object=$libelle field=nom}}</a></td>
      <td>{{mb_value object=$libelle field=services}}</td>
      <td>{{mb_value object=$libelle field=date_debut}}</td>
      <td>{{mb_value object=$libelle field=date_fin}}</td>
      <td class="text">{{mb_value object=$libelle field=mots_cles}}</td>
      <td>{{mb_value object=$libelle field=numero}}</td>
      <td>{{mb_value object=$libelle field=statut}}</td>
      <td>{{mb_value object=$libelle field=version}}</td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="8" class="empty">{{tr}}CLibelleOp.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>