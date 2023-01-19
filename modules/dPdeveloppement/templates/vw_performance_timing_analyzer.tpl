{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=developpement script=PerformanceTimingAnalyzer}}

<script>
Main.add(function(){
  var report = MbPerformance.dump();
  report.label = "Session actuelle";
  PerformanceTimingAnalyzer.loadReport(report);
})
</script>

<form name="profiling" method="get" onsubmit="return PerformanceTimingAnalyzer.analyze(this)">
  <input type="file" accept="application/json,text/json" name="export" onchange="this.form.onsubmit()" style="width: 30em;" />
</form>

<div id="profiling-report"></div>