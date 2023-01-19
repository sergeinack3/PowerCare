{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=salleOp script=geste_perop ajax=true}}

<script>
  Main.add(function () {
    var form = getForm("filter_gestes_import");

    GestePerop.userAutocomplete(form);
    GestePerop.functionAutocomplete(form);
    GestePerop.groupAutocomplete(form);
  });
</script>

<form name="filter_gestes_import" method="post" action="?m=salleOp&dosql=do_import_gestes_perop" enctype="multipart/form-data"
      onsubmit="return onSubmitFormAjax(this, {useFormAction: true});">
  <input type="hidden" name="m" value="salleOp"/>
  <input type="hidden" name="dosql" value="do_import_gestes_perop"/>

  <table class="main form">
    <tr>
      <th id="title_filter" class="title" colspan="4">{{tr}}CGestePerop-Contexts for importing Perop gestures{{/tr}}</th>
    </tr>
    <tr>
      <th>{{tr}}CGestePerop-Current group{{/tr}}</th>
      <td>
        <input type="hidden" name="current_group" value="0" />
        <input type="checkbox" name="_current_group" onchange="$V(this.form.current_group, this.checked ? 1 : 0);
          $V(this.form.function_id, '', false); $V(this.form.function_id_view, '', false);
          $V(this.form.user_id, '', false); $V(this.form.user_id_view, '', false);"/>
      </td>
    </tr>
    <tr>
      <th>{{mb_label class=CGestePerop field=function_id}}</th>
      <td>
        {{mb_field class=CGestePerop field=function_id hidden=1
        onchange="\$V(this.form.user_id, '', false); \$V(this.form.user_id_view, '', false); \$V(this.form.current_group, 0); this.form._current_group.checked = 0;
            if (this.form.group_id_view) {
               \$V(this.form.group_id_view, '', false);
             }"}}
        <input type="text" name="function_id_view"/>
        <button type="button" onclick="GestePerop.eraseInput(this.form.elements.function_id, this.form.elements.function_id_view);"
                title="{{tr}}common-action-Erase{{/tr}}">
          <i class="fas fa-eraser"></i>
        </button>
      </td>
    </tr>
    <tr>
      <th>{{mb_label class=CGestePerop field=user_id}}</th>
      <td colspan="3">
        {{mb_field class=CGestePerop field=user_id hidden=1 onchange="\$V(this.form.function_id, '', false); \$V(this.form.current_group, 0); this.form._current_group.checked = 0;
          if (this.form.function_id_view) {
            \$V(this.form.function_id_view, '', false);
          }
          if (this.form.group_id_view) {
            \$V(this.form.group_id_view, '', false);
          }"}}
        <input type="text" name="user_id_view" value="{{$filtre->_ref_user}}"/>
        <button type="button" onclick="GestePerop.eraseInput(this.form.elements.user_id, this.form.elements.user_id_view);"
                title="{{tr}}common-action-Erase{{/tr}}">
          <i class="fas fa-eraser"></i>
        </button>
      </td>
    </tr>
    <tr>
      <th></th>
      <td>
        {{mb_include module=system template=inc_inline_upload lite=true extensions='zip' multi=false paste=false}}
      </td>
    </tr>
    <tr>
      <td class="button" colspan="4">
        <button type="submit" id="button_filter" class="button tick">
          {{tr}}Import{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>
