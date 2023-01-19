{{*
 * @package Mediboard\Style\Mediboard
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if "system General system_date"|gconf}}
<script>
  (function(){
    var bind = Function.prototype.bind;
    var unbind = bind.bind(bind);

    function instantiate(constructor, args) {
      return new (unbind(constructor, null).apply(null, args));
    }

    window.DateOrig = Date;

    var systemDate = "{{'system General system_date'|gconf}}".match(/^(\d{4})-(\d{2})-(\d{2})/);
    DateOrig.systemDate = [
      parseInt(systemDate[1], 10),
      parseInt(systemDate[2], 10),
      parseInt(systemDate[3], 10)
    ];

    window.Date = function () {
      var date = instantiate(DateOrig, arguments);

      if (arguments.length == 0) {
        date.setFullYear(DateOrig.systemDate[0]);
        date.setMonth(DateOrig.systemDate[1] - 1);
        date.setDate(DateOrig.systemDate[2]);
      }

      return date;
    };

    Date.prototype = DateOrig.prototype;
  })();
</script>
{{/if}}