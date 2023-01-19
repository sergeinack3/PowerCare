{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var tabs = Control.Tabs.create('tabs-configure', true, {
      afterChange: function (container) {
        if (container.id == "CConfigEtabPatient") {
          Configuration.edit('dPpatients', ['CGroups', 'CService CGroups.group_id', 'CFunctions CGroups.group_id', 'CBlocOperatoire CGroups.group_id'], $('CConfigEtabPatient'));
        }

        if (container.id == "CConstantesMedicales") {
          new Url('patients', 'CConstantesMedicales_configure')
            .requestUpdate('CConstantesMedicales');
        }

        if (container.id == 'vw_import' && !$(container).innerHTML) {
          new Url('patients', 'vw_import')
            .requestUpdate('vw_import');
        }
      }
    });
  });
</script>

<ul id="tabs-configure" class="control_tabs me-small">
  <li><a href="#CPatient">{{tr}}CPatient{{/tr}}</a></li>
  <li><a href="#CAntecedent">{{tr}}CAntecedent{{/tr}}</a></li>
  <li><a href="#CConstantesMedicales">{{tr}}CConstantesMedicales{{/tr}}</a></li>
  <li><a href="#CConfigEtabPatient">{{tr}}config-dPatients-object-config{{/tr}}</a></li>
  <li><a href="#configure-maintenance">{{tr}}Maintenance{{/tr}}</a></li>
  <li><a href="#vw_import">{{tr}}Import{{/tr}}</a></li>
</ul>

<div id="CPatient" style="display: none;">
  {{mb_include template=CPatient_configure}}
</div>

<div id="CAntecedent" style="display: none;">
  {{mb_include template=CAntecedent_configure}}
</div>

<div id="CConstantesMedicales" style="display: none;">
  {{*{{mb_include template=CConstantesMedicales_configure}}*}}
</div>

<div id="CConfigEtabPatient" style="display: none;"></div>

<div id="configure-maintenance" style="display:none">
  {{mb_include template=inc_configure_actions}}
</div>

<div id="vw_import" style="display: none;"></div>
