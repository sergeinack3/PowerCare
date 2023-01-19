{{*
 * @package Mediboard\GestionCab
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  function printFiche(iFiche_id) {
    var url = new Url("gestionCab", "print_fiche");
    url.addParam("fiche_paie_id", iFiche_id);
    url.popup(700, 550, "Fiche");
  }

  function saveFiche() {
    var form = getForm("editFrm");
    $V(form._final_store, "1");
    form.submit();
  }
</script>

<table class="main">
  <tr>
    <td colspan="2">
      <form name="userSelector" action="?" method="get">
        <input type="hidden" name="m" value="{{$m}}" />
        {{mb_label class=CParamsPaie field=employecab_id}}
        <select name="employecab_id" onchange="this.form.submit()">
        {{foreach from=$listEmployes item=_employe}}
          <option value="{{$_employe->employecab_id}}" {{if $_employe->employecab_id == $employe->employecab_id}}selected{{/if}}>
            {{$_employe}}
          </option>
        {{/foreach}}
        </select>
      </form>

      {{if $fichePaie->_id}}
      <br />
      <a class="button new" href="?m={{$m}}&tab=edit_paie&fiche_paie_id=0">
        {{tr}}CFichePaie-title-create{{/tr}}
      </a>
      {{/if}}
    </td>
  </tr>

  <tr>
    <td class="halfPane">
      <form name="editFrm" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
        {{mb_class object=$fichePaie}}
        {{mb_key object=$fichePaie}}
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="_final_store" value="0" />
        {{mb_field object=$fichePaie field=params_paie_id hidden=1}}

        <table class="form">
          {{mb_include module=system template=inc_form_table_header object=$fichePaie}}

          <tr>
            <th>{{mb_label object=$fichePaie field="debut"}}</th>
            <td>{{mb_field object=$fichePaie field="debut" form="editFrm" register=true}}</td>
          </tr>

          <tr>
            <th>{{mb_label object=$fichePaie field="fin"}} </th>
            <td>{{mb_field object=$fichePaie field="fin" form="editFrm" register=true}}</td>
          </tr>

          <tr>
            <th>{{mb_label object=$fichePaie field="salaire"}}</th>
            <td>{{mb_field object=$fichePaie field="salaire"}}</td>
          </tr>

          <tr>
            <th>{{mb_label object=$fichePaie field="heures"}}</th>
            <td>{{mb_field object=$fichePaie field="heures"}}h</td>
          </tr>

          <tr>
            <th>{{mb_label object=$fichePaie field="heures_comp"}}</th>
            <td>{{mb_field object=$fichePaie field="heures_comp"}}h</td>
          </tr>

          <tr>
            <th>{{mb_label object=$fichePaie field="heures_sup"}}</th>
            <td>{{mb_field object=$fichePaie field="heures_sup"}}h</td>
          </tr>

          <tr>
            <th>{{mb_label object=$fichePaie field="precarite"}}</th>
            <td>{{mb_field object=$fichePaie field="precarite"}}</td>
          </tr>

          <tr>
            <th>{{mb_label object=$fichePaie field="anciennete"}}</th>
            <td>{{mb_field object=$fichePaie field="anciennete"}}</td>
          </tr>

          <tr>
            <th>{{mb_label object=$fichePaie field="conges_payes"}}</th>
            <td>{{mb_field object=$fichePaie field="conges_payes"}}</td>
          </tr>

          <tr>
            <th>{{mb_label object=$fichePaie field="prime_speciale"}}</th>
            <td>{{mb_field object=$fichePaie field="prime_speciale"}}</td>
          </tr>

          <tr>
            <td class="button" colspan="2">
              {{if !$fichePaie->_locked}}
                {{if $fichePaie->_id}}
                <button class="submit">{{tr}}Save{{/tr}}</button>
                <button class="trash" type="button" onclick="confirmDeletion(this.form, {typeName: 'la ', objName: '{{$fichePaie->_view|smarty:nodefaults|JSAttribute}}'})">
                  {{tr}}Delete{{/tr}}
                </button>
                <button class="print" type="button" onclick="printFiche(this.form.fiche_paie_id.value)">
                  {{tr}}Print{{/tr}}
                </button>
                <button class="tick" type="button" onclick="saveFiche()">
                  {{tr}}Enclose{{/tr}}
                </button>
                {{else}}
                <button class="new" type="submit">{{tr}}Create{{/tr}}</button>
                {{/if}}
              {{else}}
              <button class="print" type="button" onclick="printFiche(this.form.fiche_paie_id.value)">
                {{tr}}Print{{/tr}}
              </button>
              {{/if}}
            </td>
          </tr>
        </table>
      </form>
    </td>

    <td class="halfPane">
      <table class="form">
        <tr>
          <th class="title" colspan="3">Anciennes Fiches de paie</th>
        </tr>
        {{foreach from=$listFiches item=_fiche}}
        <tr>
          <td class="text">
            <a href="?m=gestionCab&tab=edit_paie&fiche_paie_id={{$_fiche->_id}}" onmouseover="ObjectTooltip.createEx(this, '{{$_fiche->_guid}}');">
              {{$_fiche}}
            </a>
          </td>
          <td class="button narrow">
            <button class="print" type="button" onclick="printFiche({{$_fiche->_id}})">
              {{tr}}Print{{/tr}}
            </button>

            {{if $_fiche->_locked}}
            CLOTUREE
            {{else}}
            <form name="editFrm{{$_fiche->_id}}" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
              {{mb_class object=$_fiche}}
              {{mb_key object=$_fiche}}
              <input type="hidden" name="del" value="0" />
              <button class="trash" type="button" onclick="confirmDeletion(this.form, {typeName: 'la ', objName: '{{$_fiche->_view|smarty:nodefaults|JSAttribute}}'})">
                {{tr}}Delete{{/tr}}
              </button>
            </form>
            {{/if}}
          </td>
        </tr>
        {{foreachelse}}
        <tr>
          <td class="empty">{{tr}}CFichePaie.none{{/tr}}</td>
        </tr>
        {{/foreach}}
      </table>
    </td>
  </tr>
</table>