{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main">
  <tr>
    <td>
      <form name="salle" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this)">
        {{mb_class object=$salle}}
        {{mb_key   object=$salle}}
        <input type="hidden" name="callback" value="Bloc.afterEditSalle" />
        <table class="form me-no-box-shadow me-no-align">
          <tr>
            {{mb_include module=system template=inc_form_table_header object=$salle}}
          </tr>
          <tr>
            <th>{{mb_label object=$salle field="bloc_id"}}</th>
            <td>{{mb_field object=$salle field="bloc_id" options=$blocs_list}}
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$salle field="nom"}}</th>
            <td>{{mb_field object=$salle field="nom"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$salle field="code"}}</th>
            <td>{{mb_field object=$salle field="code"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$salle field=checklist_defaut_id}}</th>
            <td>
              <select id="select_checklist_defaut" onchange="Bloc.changeChecklistDefaut($V(this))">
                <option value="0">&mdash; {{tr}}Choose{{/tr}}</option>
                <optgroup label="{{tr}}Checklist{{/tr}}">
                  {{foreach from='Ox\Mediboard\SalleOp\CDailyCheckListGroup::loadChecklistGroup'|static_call:null item=_checklist_group}}
                    <option value="{{$_checklist_group->_id}}-id" {{if $salle->checklist_defaut_id == $_checklist_group->_id}} selected{{/if}}>
                      {{$_checklist_group->title}}
                    </option>
                  {{/foreach}}
                </optgroup>
                <optgroup label="{{tr}}CDailyCheckListGroup-_type_has{{/tr}}">
                  {{foreach from='Ox\Mediboard\SalleOp\CDailyCheckList'|static:_HAS_lists key=ref_pays item=tab_list_checklist}}
                    {{if $ref_pays == $conf.ref_pays }}
                      {{foreach from=$tab_list_checklist key=_type item=_label}}
                        <option value="{{$_type}}-has" {{if $salle->checklist_defaut_has == $_type}} selected{{/if}}>
                          {{$_label}}
                        </option>
                      {{/foreach}}
                    {{/if}}
                  {{/foreach}}
                </optgroup>
              </select>
              {{mb_field object=$salle field=checklist_defaut_id hidden=true}}
              {{mb_field object=$salle field=checklist_defaut_has hidden=true}}
            </td>
          </tr>
          <tr>
            <th>{{mb_label object=$salle field="stats"}}</th>
            <td>{{mb_field object=$salle field="stats"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$salle field="dh"}}</th>
            <td>{{mb_field object=$salle field="dh"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$salle field="cheklist_man"}}</th>
            <td>{{mb_field object=$salle field="cheklist_man"}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$salle field=color}}</th>
            <td>{{mb_field object=$salle field=color form='salle'}}</td>
          </tr>
          <tr>
            <th>{{mb_label object=$salle field=actif}}</th>
            <td>{{mb_field object=$salle field=actif}}</td>
          </tr>
          <tr>
            <td class="button" colspan="2">
              {{if $salle->salle_id}}
                <button class="submit" type="button" onclick="this.form.onsubmit()">{{tr}}Save{{/tr}}</button>
                <button type="button" class="trash" onclick="confirmDeletion(this.form,{typeName:'la salle',objName: $V(this.form.nom), ajax: true})">
                  {{tr}}Delete{{/tr}}
                </button>
              {{else}}
                <button type="button" class="new" onclick="this.form.onsubmit()">
                  {{tr}}Create{{/tr}}
                </button>
              {{/if}}
            </td>
          </tr>
        </table>
      </form>
    </td>
  </tr>
</table>