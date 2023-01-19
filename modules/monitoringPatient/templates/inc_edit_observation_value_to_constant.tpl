{{*
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var form = getForm('editCObservationValueToConstant');
    new Url('patients', 'ajax_do_autocomplete_constants')
      .addParam('show_main_unit', 1)
      .addParam('show_formfields', 1)
      .autoComplete(form._search_constants, null, {
        minChars:      2,
        dropdown:      true,
        updateElement: function (selected) {
          $V(form._search_constants, selected.down('.view').getText().strip(), false);
          $V(form.constant_name, selected.get('constant'));
          $('constant_unit').update(selected.get('unit'));
        }
      });
  });
</script>

<form name="editCObservationValueToConstant" action="?" method="post"
      onsubmit="return onSubmitFormAjax(this, Control.Modal.close.curry());">
  {{mb_class object=$conversion}}
  {{mb_key object=$conversion}}

  <input type="hidden" name="group_id" value="{{$g}}">
  <input type="hidden" name="datatype" value="NM">
  
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$conversion}}
    <tr>
      <th>
        {{mb_label object=$conversion field=value_type_id}}
      </th>
      <td>
        {{mb_field  object=$conversion field=value_type_id form='editCObservationValueToConstant' autocomplete="true,1,100,true,true"}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$conversion field=value_unit_id}}
      </th>
      <td>
        {{mb_field  object=$conversion field=value_unit_id form='editCObservationValueToConstant' autocomplete="true,1,100,true,true"}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$conversion field=constant_name}}
      </th>
      <td>
        {{mb_field object=$conversion field=constant_name hidden=true}}
        <input type="text" name="_search_constants"
               value="{{if $conversion->_id}}{{tr}}CConstantesMedicales-{{$conversion->constant_name}}{{/tr}}{{/if}}">
        <span id="constant_unit">
        </span>
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$conversion field=conversion_ratio}}
      </th>
      <td>
        {{mb_field object=$conversion field=conversion_operation}}
        {{mb_field object=$conversion field=conversion_ratio}}
      </td>
    </tr>
    
    {{mb_include module=system template=inc_form_table_footer
    object=$conversion options_ajax="Control.Modal.close"}}
  </table>
</form>