{{**
 * @package Mediboard\Ucum
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 *}}

{{mb_script module="ucum" script="ucum"}}

<link href='https://clinicaltables.nlm.nih.gov/autocomplete-lhc-versions/17.0.2/autocomplete-lhc.min.css'
      rel="stylesheet">
<script src='https://clinicaltables.nlm.nih.gov/autocomplete-lhc-versions/17.0.2/autocomplete-lhc.min.js'></script>

<script>
    Main.add(function () {
        Ucum.updateConversion('{{$sourceSearch}}');
        Ucum.updateValid('{{$sourceSearch}}');
        Ucum.updateToBase('{{$sourceSearch}}');
    });
</script>

<div class="main-content" id="main-content">
    <form name="ucumForm" action="?m={{$m}}" method="post" class="prepared" onsubmit="return false">
        <div id="conversion"></div>
        <div id="validation"></div>
        <div id="toBase"></div>
    </form>
</div>

