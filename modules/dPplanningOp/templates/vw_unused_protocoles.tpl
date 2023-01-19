{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=planningOp script=protocole_cleaner ajax=$ajax}}

<script>
  Main.add(function () {
    ProtocoleCleaner.form = getForm('filterProtocoles');
    ProtocoleCleaner.initAutocompletes();
    ProtocoleCleaner.refreshList();
    ViewPort.SetAvlHeight('protocoles_area', 1.0);
  });
</script>

<form name="filterProtocoles" method="get" onsubmit="return ProtocoleCleaner.refreshList();">
  <input type="hidden" name="m" value="planningOp" />
  <input type="hidden" name="a" value="ajax_list_unused_protocoles" />

  <table class="main frm me-margin-top-8">
    <tr>
      {{me_form_field mb_object=$protocole mb_field=chir_id nb_cells=2 class=narrow}}
       {{mb_field object=$protocole field=chir_id form=filterOps hidden=true}}
        <input type="text" name="_chir_id_view" placeholder="{{tr}}CMediusers-select-praticien{{/tr}}"
               value="{{$protocole->_ref_chir->_view}}" />

        <button type="button" class="cancel notext" onclick="$V(this.form.chir_id, ''); $V(this.form._chir_id_view, '');"></button>
      {{/me_form_field}}

      {{me_form_field mb_object=$protocole mb_field=function_id nb_cells=2 class=narrow}}
        {{mb_field object=$protocole field=function_id form=filterOps hidden=true}}
        <input type="text" name="_function_id_view" placeholder="{{tr}}CMediusers-select-cabinet{{/tr}}"
               value="{{$protocole->_ref_function->_view}}" />

        <button type="button" class="cancel notext" onclick="$V(this.form.function_id, ''); $V(this.form._function_id_view, '');"></button>
      {{/me_form_field}}
    </tr>

    <tr>
      <td colspan="6" class="button">
        <button class="search me-primary">{{tr}}Filter{{/tr}}</button>
        <button type="button" class="trash" onclick="ProtocoleCleaner.deleteSelected();">{{tr}}CProtocole-Delete selected protocoles{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="protocoles_area"></div>