{{*
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Control.Tabs.create('tabs-configure', true, {
      afterChange: function (container) {
        if (container.id == "CConfigEtabGenericImport") {
          Configuration.edit('genericImport', ['CImportCampaign'], $('CConfigEtabGenericImport'));
        }
      }
    });
  });
</script>

<ul id="tabs-configure" class="control_tabs me-small">
  <li><a href="#CConfigEtabGenericImport">{{tr}}Import{{/tr}}</a></li>
  <li><a href="#config-dsn">{{tr}}common-Database{{/tr}}</a></li>
</ul>

<div id="CConfigEtabGenericImport" style="display: none;"></div>
<div id="config-dsn" style="display: none;">
    {{mb_include module=system template=configure_dsn dsn=genericImport1}}
    {{mb_include module=system template=configure_dsn dsn=genericImport2}}
    {{mb_include module=system template=configure_dsn dsn=genericImport3}}
    {{mb_include module=system template=configure_dsn dsn=genericImport4}}
    {{mb_include module=system template=configure_dsn dsn=genericImport5}}
    {{mb_include module=system template=configure_dsn dsn=genericImport6}}
    {{mb_include module=system template=configure_dsn dsn=genericImport7}}
    {{mb_include module=system template=configure_dsn dsn=genericImport8}}
    {{mb_include module=system template=configure_dsn dsn=genericImport9}}
    {{mb_include module=system template=configure_dsn dsn=genericImport10}}
</div>
