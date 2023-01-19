{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPdeveloppement script=dataModel_tmp}}

<script type="text/javascript">
  Main.add(function () {
    var form = getForm("form_details_class");
    form.onsubmit();
  })
</script>

<form name="form_details_class" method="get" onsubmit="return onSubmitFormAjax(this, null, 'details_class_div');">
  <input type="hidden" name="m" value="dPdeveloppement"/>
  <input type="hidden" name="a" value="ajax_details_class"/>
  <input type="hidden" name="class" value="{{$data_model->class_select}}"/>

  <table class="main form">
    <col style="width: 10%;"/>

    <tr>
      <th>{{mb_label object=$data_model field=show_properties}}</th>
      <td>{{mb_field object=$data_model field=show_properties onchange='this.form.onsubmit();'}}</td>

      <th>{{mb_label object=$data_model field=show_refs}}</th>
      <td>{{mb_field object=$data_model field=show_refs onchange='this.form.onsubmit();'}}</td>

      <th>{{mb_label object=$data_model field=show_formfields}}</th>
      <td>{{mb_field object=$data_model field=show_formfields onchange='this.form.onsubmit();'}}</td>

    </tr>
    <tr>
      <th>{{mb_label object=$data_model field=show_heritage}}</th>
      <td>{{mb_field object=$data_model field=show_heritage onchange='this.form.onsubmit();'}}</td>

      <th>{{mb_label object=$data_model field=show_backs}}</th>
      <td>{{mb_field object=$data_model field=show_backs onchange='this.form.onsubmit();'}}</td>

      <td colspan="2"></td>
    </tr>
  </table>
</form>

<div id="details_class_div"></div>