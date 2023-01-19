{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=info_checklist ajax=1}}

<button type="button" class="new" onclick="InfoChecklist.edit(0);">{{tr}}CInfoChecklist-title-create{{/tr}}</button>
<table class="tbl">
  <tr>
    <th class="title" colspan="4">
      {{tr}}CInfoChecklist.all{{/tr}}

      <form name="seeActif" action="?" method="get" onsubmit="InfoChecklist.seeActif(this.hide_inactif.checked ? 1 : 0);">
        <span class="compact" style="float: left;color:black;">
          <input type="checkbox" name="hide_inactif" {{if $hide_inactif}}checked="checked"{{/if}}
                 onchange="this.form.onsubmit();" />
          <label for="hide_inactif" title="{{tr}}CInfoChecklist-hide_inactif{{/tr}}">
            {{tr}}CInfoChecklist-hide_inactif{{/tr}}
          </label>
        </span>
      </form>
    </th>
  </tr>
  <tr>
    <th class="narrow">{{tr}}Action{{/tr}}</th>
    <th class="category">{{mb_title class=CInfoChecklist field=libelle}}</th>
    <th class="category">{{mb_title class=CInfoChecklist field=function_id}}</th>
    <th class="category">{{mb_title class=CInfoChecklist field=actif}}</th>
  </tr>
  <tbody id="list_info_checklists">
    {{mb_include module=cabinet template=vw_list_info_checklist}}
  </tbody>
</table>