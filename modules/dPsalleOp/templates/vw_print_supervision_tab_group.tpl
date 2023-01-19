{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Control.Tabs.create('graph_tab_group', true, {
      afterChange: function (container) {
        switch (container.id) {
          default:
          case 'graph_preop':
            SurveillancePerop.loadPeropGraphique('{{$operation->_id}}', 'preop');
            break;
          case 'graph_perop':
            SurveillancePerop.loadPeropGraphique('{{$operation->_id}}', 'perop');
            break;
          case 'graph_sspi':
            SurveillancePerop.loadPeropGraphique('{{$operation->_id}}', 'sspi');
            break;
        }
      }
    });
  });
</script>

<ul id="graph_tab_group" class="control_tabs not-printable">
  <li class="not-printable"><a href="#graph_preop">{{tr}}CDailyCheckItemCategory.type.preop{{/tr}}</a></li>
  <li class="not-printable"><a href="#graph_perop">{{tr}}CProduitLivretTherapeutique-perop{{/tr}}</a></li>
  {{if $operation->entree_reveil}}
  <li class="not-printable"><a href="#graph_sspi">{{tr}}CSupervisionGraph-type-sspi{{/tr}}</a></li>
  {{/if}}
</ul>

<div id="graph_preop"></div>
<div id="graph_perop"></div>
<div id="graph_sspi"></div>
