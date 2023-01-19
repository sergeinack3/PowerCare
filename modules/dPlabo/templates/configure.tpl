{{*
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=labo script=action}}

<script>
  Main.add(function() {
    Control.Tabs.create('tabs_config', true, {
      afterChange: function(container) {
        if (container.id == "CConfigEtab") {
          Configuration.edit('dPlabo', ['CGroups'], $('CConfigEtab'));
        }
      }
    });
  });
</script>

<ul id="tabs_config" class="control_tabs">
  <li>
    <a href="#sources">Sources</a>
  </li>
  <li>
    <a href="#CConfigEtab">{{tr}}CConfigEtab{{/tr}}</a>
  </li>
  <li>
    <a href="#tools">{{tr}}Tools{{/tr}}</a>
  </li>
</ul>

<div id="sources" style="display: none;">
  <table class="form">
    <tr>
      <th class="category">
        {{tr}}config-exchange-source{{/tr}} '{{$prescriptionlabo_source->name}}'
      </th>
    </tr>
    <tr>
      <td>{{mb_include module=system template=inc_config_exchange_source source=$prescriptionlabo_source}}</td>
    </tr>
  </table>

  <table class="form">
    <tr>
      <th class="category">
        {{tr}}config-exchange-source{{/tr}} '{{$get_id_prescriptionlabo_source->name}}'
      </th>
    </tr>
    <tr>
      <td>{{mb_include module=system template=inc_config_exchange_source source=$get_id_prescriptionlabo_source}}</td>
    </tr>
  </table>
</div>

<div id="CConfigEtab" style="display: none;"></div>

<div id="tools">
  <table class="form">
    <!-- CCatalogueLabo -->
    {{assign var="class" value="CCatalogueLabo"}}

    <tr>
      <th class="category" colspan="100">{{tr}}{{$class}}{{/tr}}</th>
    </tr>

    <tr>
      <td><button class="tick" onclick="Action.update('importCatalogues')">Importer</button></td>
      <td id="action-importCatalogues"></td>
    </tr>

    <!-- CPack -->
    {{assign var="class" value="CPackExamensLabo"}}

    <tr>
      <th class="category" colspan="100">{{tr}}{{$class}}{{/tr}}</th>
    </tr>

    <tr>
      <td><button class="tick" onclick="Action.update('importPacks')">Importer</button></td>
      <td id="action-importPacks"></td>
    </tr>
  </table>
</div>