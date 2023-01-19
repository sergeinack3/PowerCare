{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=auto_refresh_frequency value='dPadmissions automatic_reload auto_refresh_frequency_identito'|gconf}}

{{mb_script module=admissions script=identito_vigilance}}

<script>
  onMergeComplete = function() {
    IdentitoVigilance.start(0, '{{$auto_refresh_frequency}}');
  };

  togglePlayPause = function(button) {
    button.toggleClassName("play");
    button.toggleClassName("pause");
    if (button.hasClassName("play")) {
      IdentitoVigilance.stop();
    }
    else {
      IdentitoVigilance.resume();
    }
  };

  Main.add(function() {
    IdentitoVigilance.date = "{{$date}}";
    IdentitoVigilance.start(2, '{{$auto_refresh_frequency}}');

    Control.Tabs.create('tab_admissions_identito_vigilance', false);
  });
</script>

<ul id="tab_admissions_identito_vigilance" class="control_tabs">
  <li><a href="#identito_vigilance" class="empty">{{tr}}Identito-vigilance{{/tr}} <small>(&ndash;)</small></a></li>
  <li style="width: 20em; text-align: center">
    <script>
    Main.add(function() {
      Calendar.regField(getForm("changeDate").date, null, {noView: true} );
    } );
    </script>
    <strong><big>{{$date|date_format:$conf.longdate}}</big></strong>
    
    <form action="?" name="changeDate" method="get">
      <input type="hidden" name="m" value="{{$m}}" />
      <input type="hidden" name="tab" value="{{$tab}}" />
      <input type="hidden" name="date" class="date" value="{{$date}}" onchange="this.form.submit();" />
    </form>
  </li>
  <li>
    <button type="button" class="pause notext" onclick="togglePlayPause(this);" style="float: right;"
      title="{{tr}}CAffectation-play_pause_temporel{{/tr}}"></button>
  </li>
</ul>

<div id="identito_vigilance" style="display: none; margin: 0 5px;" class="me-no-border">
  <div class="small-info">{{tr}}msg-common-loading-soon{{/tr}}</div>
</div>