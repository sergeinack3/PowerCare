{{*
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Control.Tabs.create('tabs-etab-details', true);
  });

  openFieldDetails = function(page) {
    var url = new Url('openData', 'vw_field_details');
    url.addParam('page', page);
    url.requestModal(900, '80%')
  }
</script>

<div>
  <h2>{{$etab->raison_sociale}} : {{$etab->finess}}</h2>
</div>

<table class="main layout">
  <tr>
    <td width="10%">
      <ul class="control_tabs_vertical" id="tabs-etab-details">
        <li><a href="#tab-vw-hd-identite">{{tr}}mod-openData-vw-hd-identite{{/tr}}</a></li>
        <li><a href="#tab-vw-perfs-activite">{{tr}}mod-openData-vw-perfs-activite{{/tr}}</a></li>
        <li><a href="#tab-vw-perfs-qualite">{{tr}}mod-openData-vw-perfs-qualite{{/tr}}</a></li>
        <li><a href="#tab-vw-perfs-orga">{{tr}}mod-openData-vw-perfs-orga{{/tr}}</a></li>
        <li><a href="#tab-vw-perfs-rh">{{tr}}mod-openData-vw-perfs-rh{{/tr}}</a></li>
        <li><a href="#tab-vw-perfs-finance">{{tr}}mod-openData-vw-perfs-finance{{/tr}}</a></li>
      </ul>
    </td>
    <td>
      <div id="tab-vw-hd-identite" style="display: none;">
        {{mb_include module=openData template=vw_hd_identite}}
      </div>
      <div id="tab-vw-perfs-activite" style="display: none;">
        {{mb_include module=openData template=vw_perfs_hd fields=$activites labels=$activite_fields class='CHDActivite'
        pages=$activites_pages}}
        <br/>
        {{mb_include module=openData template=vw_perfs_hd_za fields=$activites_zone labels=$activite_zone_fields
        pages=$activites_zone_pages}}
      </div>
      <div id="tab-vw-perfs-qualite" style="display: none;">
        {{mb_include module=openData template=vw_perfs_hd fields=$qualites labels=$qualite_fields class='CHDQualite'
        pages=$qualites_pages}}
      </div>
      <div id="tab-vw-perfs-orga" style="display: none;">
        {{mb_include module=openData template=vw_perfs_hd fields=$orgas labels=$orga_fields class='CHDProcess'
        pages=$orgas_pages}}
      </div>
      <div id="tab-vw-perfs-rh" style="display: none;">
        {{mb_include module=openData template=vw_perfs_hd fields=$resshums labels=$resshum_fields class='CHDResshum'
        pages=$resshums_pages}}
      </div>
      <div id="tab-vw-perfs-finance" style="display: none;">
        {{mb_include module=openData template=vw_perfs_hd fields=$finances labels=$finance_fields class='CHDFinance'
        pages=$finances_pages}}
      </div>
    </td>
  </tr>
</table>