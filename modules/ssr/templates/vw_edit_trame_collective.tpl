{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    TrameCollective.autocompleteElementPrescription(getForm('Edit-{{$trame->_guid}}'));
  });
</script>

<form name="Edit-{{$trame->_guid}}" action="" method="post" onsubmit="return TrameCollective.onsubmit(this);">
  <input type="hidden" name="del" value="0" />
  {{mb_key   object=$trame}}
  {{mb_class object=$trame}}
  {{mb_field object=$trame field=group_id hidden=true}}
  {{mb_field object=$trame field=type hidden=true}}
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$trame}}
    <tr>
      <th>{{mb_label object=$trame field=function_id}}</th>
      <td>{{mb_field object=$trame field=function_id options=$functions emptyLabel="CFunctions.select"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$trame field=nom}}</th>
      <td>{{mb_field object=$trame field=nom}}</td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        {{if $trame->_id}}
          <button class="modify me-primary" type="button" onclick="this.form.onsubmit();">{{tr}}Save{{/tr}}</button>
          <button class="trash" type="button" onclick="TrameCollective.confirmDeletion(this.form);">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>