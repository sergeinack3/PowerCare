{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cim10 script=CIM}}

<script type="text/javascript">
  Main.add(function() {
    CIM.initializeView();
  });
</script>

<div id="quick_search_container">
  <form name="quickSearchCim" method="post" action="?" onsubmit="">
    <input type="text" name="keywords_code" class="autocomplete" value="" placeholder="{{tr}}CCodeCIM10-action-search{{/tr}}">
    <button type="button" class="search notext" onclick="CIM.viewSearch(CIM.showCode.bind(CIM));">
      {{tr}}Advanced-Search{{/tr}}
    </button>
    <button type="button" onclick="CIM.manageFavoris();" title="{{tr}}CFavoriCIM10-action-manage{{/tr}}">
      <i class="fa fa-lg fa-star" style="color: goldenrod;"></i>
    </button>
  </form>
</div>

<div id="summary_placeholder">
  {{mb_include module=cim10 template=cim/inc_summary}}
</div>

<div id="cim10_details_placeholder">
  <fieldset>
    <legend>{{tr}}CCIM10-title-code{{/tr}}</legend>
    <div id="cim10_details">
      {{mb_include module=cim10 template=cim/inc_details}}
    </div>
  </fieldset>
</div>
