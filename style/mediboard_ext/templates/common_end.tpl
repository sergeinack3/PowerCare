{{*
 * @package Mediboard\Style\Mediboard
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=multi_tab_msg_read value=0}}

{{mb_include style=mediboard_ext template=navigation_history}}

<script>
  {{assign var=end_app value='Ox\Core\CMbPerformance::end'|static_call:""}}

  {{* Send timing data in HTTP header *}}
  {{assign var=timer value='Ox\Core\CMbPerformance::out'|static_call:""}}
  {{assign var=request_uid value='Ox\Core\CApp::getRequestUID'|static_call:""}}
  {{mb_default var=dosql value=''}}

  // Perfomance log
  if (MbPerformance.timingSupport) {
    (function(){
      var offset = (performance.timing.responseEnd - performance.timing.fetchStart);
      var serverTiming = {{$timer|@json}};
      var page = {
        m: "{{$m}}",
        a: "{{$dosql|ternary:$dosql:$action}}",
        id: 0,
        guid: "{{$request_uid}}"
      };

      var timing = MbPerformance.parseServerTiming(MbPerformance.readCookie("timing"));
      var serverTime = serverTiming.start;

      if (timing) {
        serverTime = timing.start;

        serverTiming.handlerStart = timing.start + MbPerformance.timeOffset;
        serverTiming.handlerEnd   = serverTiming.handlerStart + timing.duration;
      }

      MbPerformance.timeOffset = Math.round(performance.timing.requestStart - serverTime);

      MbPerformance.pageDetail = serverTiming;
      MbPerformance.addEvent("load", function(){
        MbPerformance.logScriptEvent.defer("page", page, serverTiming, 0, offset);
      });
    })();
  }

  Main.add(function() {
    MultiTabChecker.check('{{$conf.debug}}', '{{$multi_tab_msg_read}}');
  });
</script>

</body>
</html>
