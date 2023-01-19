{{*
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=sante400 script=hyperTextLink ajax=true}}

<script>
  reloadListHypertextLinks = function () {
    Control.Modal.close();

    {{if $hypertext_link->object_class == "CAideSaisie"}}
    AideSaisie.removeLocalStorage();
    {{/if}}

    HyperTextLink.getListFor('{{$hypertext_link->object_id}}', '{{$hypertext_link->object_class}}', '{{$show_widget}}');
    return false;
  }
</script>

<form name="edit-hypertext_link" method="post" onsubmit="return onSubmitFormAjax(this, reloadListHypertextLinks);">
  {{mb_key object=$hypertext_link}}
  {{mb_class object=$hypertext_link}}
  <input type="hidden" name="del" value="0"/>
  {{mb_field object=$hypertext_link field="object_id" hidden=true}}
  {{mb_field object=$hypertext_link field="object_class" hidden=true}}

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$hypertext_link}}
    <tr>
      <th>{{mb_label object=$hypertext_link field=name}}</th>
      <td>{{mb_field object=$hypertext_link field=name}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$hypertext_link field=link}}</th>
      <td>{{mb_field object=$hypertext_link field=link}}</td>
    </tr>
    <tr>
      <td colspan="2" style="text-align: center;">
        <button type="submit" class="save">{{tr}}Save{{/tr}}</button>
        {{if $hypertext_link->_id}}
          <button type="button" class="trash" onclick="confirmDeletion(this.form, {
            typeName: 'le lien hypertexte',
            objName: '{{$hypertext_link->name|smarty:nodefaults|JSAttribute}}'
            },
            {ajax: true, onComplete: reloadListHypertextLinks});">{{tr}}Delete{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>