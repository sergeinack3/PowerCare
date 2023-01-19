{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=type value="preop"}}
<form name="editPoste{{$type}}{{$_operation->_id}}" method="post" class="prepared">
  <input type="hidden" name="m" value="planningOp" />
  <input type="hidden" name="dosql" value="do_planning_aed" />
  {{mb_key object=$_operation}}
  <input type="hidden" name="poste_preop_id" value="{{$_operation->poste_preop_id}}"
         onchange="onSubmitFormAjax(this.form, {onComplete:function () {
           refreshTabReveil('{{$type}}');

         {{if "patientMonitoring"|module_active && "patientMonitoring CMonitoringSession start_monitoring_session"|gconf}}
            App.loadJS({module: 'patientMonitoring', script: 'concentrator_common'}, function() {
              ConcentratorCommon.askPosteConcentrator(
                '{{$_operation->_id}}',
                '{{if $_operation->_ref_salle}}{{$_operation->_ref_salle->bloc_id}}{{/if}}',
                '{{$type}}',
           getForm('editPoste{{$type}}{{$_operation->_id}}'),
           refreshTabReveil.curry('{{$type}}'),
           1,
           '{{$sspi_id}}');
            });
         {{/if}}

         }});"/>
  <input type="text" name="_poste_preop_id_autocomplete" value="{{$_operation->_ref_poste_preop}}"/>
  <button type="button" class="erase notext me-tertiary me-small" title="{{tr}}Clear{{/tr}}"
          onclick="$V(this.form._poste_preop_id_autocomplete, '');$V(this.form.poste_preop_id, '');"></button>
  <script>
    Main.add(function() {
      var form = getForm("editPoste{{$type}}{{$_operation->_id}}");
      var sspi_id = $V(getForm('selectBloc').sspi_id);

      var url = new Url("salleOp", "sspipost_autocomplete");
      {{if "dPsalleOp SSPI see_sspi_bloc"|gconf}}
        url.addParam("sspi_id", sspi_id);
      {{/if}}
      url.autoComplete(form.elements._poste_preop_id_autocomplete, null, {
        minChars: 2,
        method: "get",
        select: "view",
        dropdown: true,
        afterUpdateElement: function(field,selected) {
          var guid = selected.getAttribute('id');
          if (guid) {
            $V(field.form['poste_preop_id'], guid.split('-')[2]);
          }
        }
      });
    });
  </script>
</form>
