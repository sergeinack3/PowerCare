{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="dPpatients" script="pat_selector"}}

<script type="text/javascript">
  function IPPconflict() {
    var url = new Url("dPpatients", "ajax_ipp_conflicts");
    url.requestUpdate("ipp-conflicts");
  }

  onMergeComplete = function () {
    IPPconflict();
  };

  Main.add(Control.Tabs.create.curry('tabs-identito-vigilance', true));

  Main.add(function () {
    IPPconflict();
  });
</script>

<ul id="tabs-identito-vigilance" class="control_tabs">
  <li>
    <a href="#similar">Patients similaires</a>
  </li>
  <li>
    <a href="#matching">Patients identiques</a>
  </li>
  <li>
    <a class="{{if $count_conflicts == 0}}empty{{else}}wrong{{/if}}" href="#ipp-conflict">
      IPP conflits ({{$count_conflicts}})
    </a>
  </li>
</ul>

<div id="similar" style="display: none;">
  {{mb_include template=inc_similar_patients}}
</div>

<div id="matching" style="display: none;">
  {{mb_include template=inc_matching_patients}}
</div>

<div id="ipp-conflict" style="display: none;">
  <table class="tbl" id="ipp-conflicts"></table>
</div>