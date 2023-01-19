{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=inc_pagination total=$total_categories current=$page change_page='FilesCategory.changePage'}}

<table class="tbl" id="list_file_categories">
  <tr>
    {{if $can->admin}}
      <th class="narrow">
        <button class="merge notext button me-tertiary" onclick="FilesCategory.mergeSelected()">fusion</button>
      </th>
    {{/if}}
    <th class="narrow"></th>
    <th class="narrow">Etiquettes</th>
    <th class="narrow">{{mb_title class=CFilesCategoryToReceiver field=receiver_id}}</th>
    <th {{if !$can->admin}}colspan="2"{{/if}}>{{mb_title class=CFilesCategory field=nom}}</th>
    <th>{{mb_title class=CFilesCategory field=class}}</th>
    <th>{{mb_title class=CFilesCategory field=group_id}}</th>
    <th>{{mb_title class=CFilesCategory field=send_auto}}</th>
    <th>{{mb_title class=CFilesCategory field=eligible_file_view}}</th>
    {{if "dmp"|module_active && 'Ox\Interop\Dmp\CDMP::getAuthentificationType'|static_call:""}}
      <th>{{mb_title class=CFilesCategory field=type_doc_dmp}}</th>
    {{/if}}
    {{if "sisra"|module_active}}
      <th>{{mb_title class=CFilesCategory field=type_doc_sisra}}</th>
    {{/if}}
    <th class="narrow">
      Nombre d'utilisateurs / cabinets <br />
      Catégorie par défaut
    </th>
  </tr>

  {{foreach from=$categories item=_category}}
    {{assign var=class_important value=""}}
    {{if $_category->importance == "high"}}
      {{assign var=class_important value="ok"}}
    {{/if}}

    {{mb_include template="inc_vw_files_category"}}
  {{/foreach}}
</table>