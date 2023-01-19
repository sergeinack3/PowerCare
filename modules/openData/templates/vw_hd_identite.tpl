{{*
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Control.Tabs.create('tabs-etab-details-identite', true);
  });
</script>

<ul class="control_tabs" id="tabs-etab-details-identite">
  <li><a href="#tab-vw-details-identite-volumetrie">{{tr}}mod-openData-vw-details-identite-volumetrie{{/tr}}</a></li>
  <li><a href="#tab-vw-details-identite-intervention">{{tr}}mod-openData-vw-details-identite-intervention{{/tr}}</a></li>
  <li><a href="#tab-vw-details-identite-activite">{{tr}}mod-openData-vw-details-identite-activite{{/tr}}</a></li>
  <li><a href="#tab-vw-details-identite-infrastructure">{{tr}}mod-openData-vw-details-identite-infrastructure{{/tr}}</a></li>
  <li><a href="#tab-vw-details-identite-informatisation">{{tr}}mod-openData-vw-details-identite-informatisation{{/tr}}</a></li>
  <li><a href="#tab-vw-details-identite-finances">{{tr}}mod-openData-vw-details-identite-finances{{/tr}}</a></li>
  <li><a href="#tab-vw-details-identite-rh">{{tr}}mod-openData-vw-details-identite-rh{{/tr}}</a></li>
</ul>

<div id="tab-vw-details-identite-volumetrie" style="display: none;">
  {{mb_include module=openData template=vw_perfs_hd fields=$identites_volumetrie labels=$identites_volumetrie_fields
    class='CHDIdentite' pages=$identite_pages}}
</div>
<div id="tab-vw-details-identite-intervention" style="display: none;">
  {{mb_include module=openData template=vw_perfs_hd fields=$identites_interv labels=$identites_interv_fields class='CHDIdentite'
   pages=$identite_pages}}
</div>
<div id="tab-vw-details-identite-activite" style="display: none;">
  {{mb_include module=openData template=vw_perfs_hd fields=$identites_acts labels=$identites_acts_fields class='CHDIdentite'
    use_ga=true pages=$identite_pages}}
</div>
<div id="tab-vw-details-identite-infrastructure" style="display: none;">
  {{mb_include module=openData template=vw_perfs_hd fields=$identites_infras labels=$identites_infras_fields class='CHDIdentite'
    pages=$identite_pages}}
</div>
<div id="tab-vw-details-identite-informatisation" style="display: none;">
  {{mb_include module=openData template=vw_perfs_hd fields=$identites_infos labels=$identites_infos_fields class='CHDIdentite'
    pages=$identite_pages}}
</div>
<div id="tab-vw-details-identite-finances" style="display: none;">
  {{mb_include module=openData template=vw_perfs_hd fields=$identites_finances labels=$identites_finances_fields class='CHDIdentite'
    finance=true ignore_field='coeff_transition' pages=$identite_pages}}
</div>
<div id="tab-vw-details-identite-rh" style="display: none;">
  {{mb_include module=openData template=vw_perfs_hd fields=$identites_rhs labels=$identites_rhs_fields class='CHDIdentite'
    pages=$identite_pages}}
</div>