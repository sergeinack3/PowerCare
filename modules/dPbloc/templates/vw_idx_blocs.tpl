{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=bloc script=bloc ajax=true}}

<script>
  Main.add(function() {
    Control.Tabs.create('tabs-bloc', true, {afterChange: function(container) {
        switch(container.id) {
          case 'blocs':
            Bloc.displayListBlocs({{$bloc}});
            break;
          case 'salles':
            Bloc.displayListSalles({{$salle}});
            break;
          case 'sspis':
            Bloc.displayListSSPIS({{$sspi}});
            break;
          case 'postes_preop':
            Bloc.displayListPostesPreop();
          default:
        }
      }
    });
  });
</script>

{{assign var=use_poste value=$conf.dPplanningOp.COperation.use_poste}}

<ul id="tabs-bloc" class="control_tabs">
  <li><a href="#blocs">{{tr}}CBlocOperatoire{{/tr}}</a></li>
  <li><a href="#salles">{{tr}}CSalle{{/tr}}</a></li>
  {{if $use_poste}}
    <li><a href="#sspis">{{tr}}CSSPI{{/tr}}</a></li>
    {{if $postes_no_sspi}}
      <li><a href="#postes_preop">{{tr}}CPosteSSPI-Postes preop{{/tr}}</a></li>
    {{/if}}
  {{/if}}
  <li><button type="button" style="float:right;" onclick="return Bloc.popupImport();" class="hslip">{{tr}}Import-CSV{{/tr}}</button></li>
</ul>

<div id="blocs" style="display: none;"></div>

<div id="salles" style="display: none;"></div>

{{if $use_poste}}
  <div id="sspis" style="display: none;"></div>
  {{if $postes_no_sspi}}
    <div id="postes_preop" style="display: none;"></div>
  {{/if}}
{{/if}}