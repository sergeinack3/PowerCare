{{*
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=monitoringPatient script=param_surveillance ajax=1}}
<div class="small-warning">
  Ces paramètres <strong>ne sont pas</strong> ceux qui sont utilisés dans les volets "Surveillance" ou "Constantes". <br />
  Il sont utilisés pour la récupération automatique depuis des systèmes automatiques de mesures (au bloc par exemple).
</div>

<script>
  Main.add(function () {
    Control.Tabs.create("main_tabs_types", true);
    ParamSurveillance.list('CObservationValueType');
    ParamSurveillance.list('CObservationValueUnit');
    ParamSurveillance.listConversion();
  });
</script>

<ul id="main_tabs_types" class="control_tabs">
  <li><a href="#list-CObservationValueType">Types</a></li>
  <li><a href="#list-CObservationValueUnit">Unités</a></li>
  <li><a href="#list-CObservationValueToConstant">Correspondance constantes Mediboard</a></li>
</ul>

<div id="list-CObservationValueType" style="display: none;"></div>
<div id="list-CObservationValueUnit" style="display: none;"></div>
<div id="list-CObservationValueToConstant" style="display: none;"></div>
