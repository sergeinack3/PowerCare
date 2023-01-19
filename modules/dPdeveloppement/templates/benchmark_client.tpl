{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">

var Chronometer = Class.create({
  startTime: null,
  stopTime: null,
  duration: null,

  start: function() {
    this.startTime = new Date;
  },
  
  stop: function() {
    this.stopTime = new Date;
    this.duration = this.stopTime - this.startTime;
  }
});

var Benchmark = {
  module     : "dPdeveloppement", 
  action     : "css_test",
  chronometer: null,

  start: function() {
    this.chronometer = new Chronometer;
    this.chronometer.start();
    this.play();
    this.chronometer.stop();
  },
  
  play: function() {
    var oChrono = new Chronometer;
    
    var oOptions = {
      onLoading: oChrono.start(),
      onComplete: function() {
        oChrono.stop();
        $("loadajaxdatastime").innerHTML = oChrono.duration + "ms";
        Benchmark.modifyDom();
      }
    };
  
    var url = new Url(this.module, this.action);
    url.requestUpdate("response", oOptions);
  },
  
  modifyDom: function() {
    var oChrono = new Chronometer(this);
    oChrono.start();
    for(i=0; i<30; i++) {
      $('response').select('td').each(function(element) {element.hide()});
      $('response').select('td').each(function(element) {element.show()});
      $('response').select('div').each(function(element) {new Draggable(element)});
      $('response').select('form').each(function(element) {element.removeClassName('prepared'); prepareForm(element)});
    }
    oChrono.stop();
    $("modifydomtime").innerHTML = oChrono.duration + "ms";
  },
}

Main.add( function() {
  Benchmark.start();
});

</script>

<h2>Analyse de performance du navigateur</h2>
<table class="tbl">
  <tr>
    <th>Action</th>
    <th>Decription</th>
    <th>Durée</th>
  </tr>
  <tr>
    <td>LoadAjaxDatas()</td>
    <td>Chargement ajax de la page de test CSS</td>
    <td id="loadajaxdatastime"></td>
  </tr>
  <tr>
    <td>modifyDom()</td>
    <td>Modification de certains élément de la DOM</td>
    <td id="modifydomtime"></td>
  </tr>
</table>

<div id="response" style="display: none"></div>