{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  updatePoursuite = function(statut) {
    if (statut.value == "poursuite") {
      $('change_poursuite').show();
    }
    else {
      $('change_poursuite').hide();
      var form = getForm('Edit-CRelance');
      form.poursuite.value = "";
    }
  }
</script>
<form name="Edit-CRelance" action="?m={{$m}}" method="post" onsubmit="onSubmitFormAjax(this);">
  {{mb_key    object=$relance}}
  {{mb_class  object=$relance}}
  <input type="hidden" name="del" value="0"/>
  <input type="hidden" name="object_id"    value="{{$relance->object_id}}"/>
  <input type="hidden" name="object_class" value="{{$relance->object_class}}"/>
  <table class="form">
  {{mb_include module=system template=inc_form_table_header object=$relance}}
  <tr>
    <th>{{mb_label object=$relance field=date}}</th>
    <td>{{mb_field object=$relance field=date form="Edit-CRelance" canNull="false" register=true}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$relance field=du_patient}}</th>
    <td>{{mb_field object=$relance field=du_patient}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$relance field=du_tiers}}</th>
    <td>{{mb_field object=$relance field=du_tiers}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$relance field=etat}}</th>
    <td>{{mb_field object=$relance field=etat}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$relance field=statut}}</th>
    <td>{{mb_field object=$relance field=statut emptyLabel=Choose onchange="updatePoursuite(this);"}}</td>
  </tr>
  <tr {{if $relance->statut != "poursuite"}} style="display:none;" {{/if}} id="change_poursuite">
    <th>{{mb_label object=$relance field=poursuite}}</th>
    <td>{{mb_field object=$relance field=poursuite emptyLabel=Choose}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$relance field=statut_envoi}}</th>
    <td>{{mb_field object=$relance field=statut_envoi}}</td>
  </tr>

  <tr>
    <td class="button" colspan="2">
      {{if $relance->_id}}
      <button class="submit" type="submit">{{tr}}Save{{/tr}}</button>
      <button class="trash" type="reset" onclick="confirmDeletion(this.form,{typeName:'la relance du',objName: $V(this.form.date) })">
        {{tr}}Delete{{/tr}}
      </button>
      {{else}}
      <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
      {{/if}}
    </td>
  </tr>
  </table>
</form>