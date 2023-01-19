{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main">
  <tr>
    <td>
      <form name="bloc-edit" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this)">
        {{mb_class object=$bloc}}
        {{mb_key   object=$bloc}}
        <input type="hidden" name="group_id" value="{{$g}}" />
        <input type="hidden" name="callback" value="Bloc.afterEditBloc" />
        <table class="form me-no-box-shadow me-no-align">
          {{mb_include module=system template=inc_form_table_header object=$bloc}}
          <tr>
            <th class="halfPane">{{mb_label object=$bloc field="nom"}}</th>
            <td>{{mb_field object=$bloc field="nom"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$bloc field="type"}}</th>
            <td>{{mb_field object=$bloc field="type"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$bloc field="tel"}}</th>
            <td>{{mb_field object=$bloc field="tel"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$bloc field="days_locked"}}</th>
            <td>{{mb_field object=$bloc field="days_locked"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$bloc field="use_brancardage"}}</th>
            <td>{{mb_field object=$bloc field="use_brancardage"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$bloc field="presence_preop_ambu"}}</th>
            <td>{{mb_field object=$bloc field="presence_preop_ambu" form="bloc-edit"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$bloc field="duree_preop_ambu"}}</th>
            <td>{{mb_field object=$bloc field="duree_preop_ambu" form="bloc-edit"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$bloc field="checklist_everyday"}}</th>
            <td>{{mb_field object=$bloc field="checklist_everyday"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$bloc field="actif"}}</th>
            <td>{{mb_field object=$bloc field="actif"}}</td>
          </tr>
          <tr>
            <td class="button" colspan="2">
              {{if $bloc->_id}}
                <button class="submit" type="button" onclick="this.form.onsubmit()">{{tr}}Save{{/tr}}</button>
                <button type="button" class="trash" onclick="confirmDeletion(this.form,{objName:'{{$bloc->nom|smarty:nodefaults|JSAttribute}}', ajax: true})">
                  {{tr}}Delete{{/tr}}
                </button>
              {{else}}
                <button type="button" class="new" onclick="this.form.onsubmit()">{{tr}}Create{{/tr}}</button>
              {{/if}}
            </td>
          </tr>
        </table>
      </form>
    </td>
  </tr>
</table>