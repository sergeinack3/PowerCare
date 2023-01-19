{{*
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function(){
    Control.Tabs.create('monitoring-patient-tabs', true, {afterChange: function(container) {
        if (container.id == "CConfigEtab") {
          Configuration.edit('monitoringPatient', ['CGroups'], $('CConfigEtab'));
        }
      }});
  });
</script>

<ul id="monitoring-patient-tabs" class="control_tabs">
  <li><a href="#CConfigEtab">{{tr}}CConfigEtab{{/tr}}</a></li>
</ul>

<div id="CConfigEtab" style="display: none;"></div>
