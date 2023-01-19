{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=type value="ops"}}

{{assign var=use_concentrator value=false}}
{{assign var=use_concentrator value=""}}
{{if "patientMonitoring"|module_active && "patientMonitoring CMonitoringConcentrator active"|gconf}}
  {{assign var=use_concentrator value=true}}
  {{assign var=current_session value='Ox\Mediboard\PatientMonitoring\CMonitoringSession::getCurrentSession'|static_call:$_operation}}
{{/if}}
<form name="editPoste{{$type}}{{$_operation->_id}}" method="post" class="prepared">
  <input type="hidden" name="m" value="planningOp" />
  <input type="hidden" name="dosql" value="do_planning_aed" />
  {{mb_key object=$_operation}}
  <input type="hidden" name="poste_sspi_id" value="{{$_operation->poste_sspi_id}}"
         onchange="onSubmitFormAjax(this.form, refreshTabReveil.curry('{{$type}}'))"/>
  <input type="text" name="_poste_sspi_id_autocomplete" value="{{$_operation->_ref_poste}}"/>
  <button type="button" class="erase notext me-tertiary me-small" title="{{tr}}Clear{{/tr}}"
          onclick="$V(this.form._poste_sspi_id_autocomplete, '');$V(this.form.poste_sspi_id, '');"></button>
  <script>
    Main.add(function() {
      var form = getForm("editPoste{{$type}}{{$_operation->_id}}");
      var poste_sspi_id = $V(form.poste_sspi_id);
      var url = new Url("system", "ajax_seek_autocomplete");
      url.addParam("object_class", "CPosteSSPI");
      url.addParam('show_view', true);
      url.addParam("input_field", "_poste_sspi_id_autocomplete");
      url.addParam("where[type]", "sspi");
      url.addParam("where[actif]", 1);
    {{if "dPsalleOp SSPI see_sspi_bloc"|gconf}}
        url.addParam("where[sspi_id]", "{{$sspi_id}}");
      {{else}}
        url.addParam("ljoin[sspi]", "sspi.sspi_id = poste_sspi.sspi_id");
        url.addParam("where[sspi.group_id]", "{{$g}}");
      {{/if}}
      url.addParam("limit", 100);
      url.autoComplete(form.elements._poste_sspi_id_autocomplete, null, {
        minChars: 2,
        method: "get",
        select: "view",
        dropdown: true,
        afterUpdateElement: function(field,selected) {
          var guid = selected.getAttribute('id');
          if (guid) {
            $V(field.form['poste_sspi_id'], guid.split('-')[2]);
          }

         {{if ($use_concentrator && $current_session) || ("patientMonitoring"|module_active && "patientMonitoring CMonitoringSession start_monitoring_session"|gconf)}}
          if (poste_sspi_id != guid.split('-')[2]) {
            App.loadJS({module: "patientMonitoring", script: "concentrator_common"}, function(){
              ConcentratorCommon.askPosteConcentrator(
                "{{$_operation->_id}}",
                "{{$bloc_id}}",
                "sspi",
                form,
                refreshTabReveil.curry('{{$type}}'),
               1
              );
            });
          }
        {{/if}}
        }
      });
    });
  </script>
</form>
