{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="Edit-CLibelleOp" method="post" onsubmit="return onSubmitFormAjax(this, {onComplete: Control.Modal.close});">
  <input type="hidden" name="m" value="{{$m}}" />
  {{mb_key    object=$libelle}}
  {{mb_class  object=$libelle}}
  <input type="hidden" name="del" value="0"/>
  {{mb_field object=$libelle field=group_id hidden=true}}

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$libelle}}
    <tr>
      <th>{{mb_label object=$libelle field=numero}}</th>
      <td>{{mb_field object=$libelle field=numero}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$libelle field=nom}}</th>
      <td>{{mb_field object=$libelle field=nom}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$libelle field=date_debut}}</th>
      <td>{{mb_field object=$libelle field=date_debut form="Edit-CLibelleOp" canNull="true" register=true}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$libelle field=date_fin}}</th>
      <td>{{mb_field object=$libelle field=date_fin form="Edit-CLibelleOp" canNull="true" register=true}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$libelle field=services}}</th>
      <td>{{mb_field object=$libelle field=services}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$libelle field=mots_cles}}</th>
      <td>{{mb_field object=$libelle field=mots_cles}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$libelle field=version}}</th>
      <td>{{mb_field object=$libelle field=version}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$libelle field=statut}}</th>
      <td>{{mb_field object=$libelle field=statut}}</td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        {{if $libelle->_id}}
          <button class="submit" type="submit">{{tr}}Save{{/tr}}</button>
          <button class="trash" type="reset" onclick="confirmDeletion(this.form,{typeName:'le libellé ',objName: $V(this.form.nom) })">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>