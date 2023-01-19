{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">

var Chronometer = Class.create();

Chronometer.prototype = {
  benchmark: null,

  startTime: null,
  stopTime: null,
  duration: null,

  initialize: function(benchmark) {
    this.benchmark = benchmark;
  },

  start: function() {
    this.startTime = new Date;
    this.benchmark.start();
  },
  
  stop: function() {
    this.stopTime = new Date;
    this.duration = this.stopTime - this.startTime;
    this.benchmark.stop(this.duration);
  }
}

var Benchmark = {
  module : "{{$module}}", 
  action: "{{$action}}",
  
  totalDuration : 0,
  requestCount : 0,
  responseCount : 0,
  averageDuration : 0,
  
  startTime: null,
  stopTime: null,
  duration: null,
  
  executer : null,

  start: function() {
  	this.requestCount++;
    $("requestCount").innerHTML = this.requestCount;
  },
  
  stop: function (duration) {
  	this.responseCount++;
    this.totalDuration += duration;
    this.averageDuration = this.totalDuration / this.responseCount;
    $("responseCount").innerHTML = this.responseCount;
    $("lastDuration").innerHTML = duration;
    $("averageDuration").innerHTML = Math.round(this.averageDuration);
  },
  
  send: function() {
    var oChrono = new Chronometer(this);
    
    var oOptions = {
      onLoading: oChrono.start.bind(oChrono),
      onComplete: oChrono.stop.bind(oChrono)
    };
  
    var url = new Url(this.module, this.action);
    url.requestUpdate("response", oOptions);
  },
  
  sendEvery: function(fMilliseconds) {
  	fMilliseconds = parseFloat(fMilliseconds);
    
    if (this.executer) {
      this.executer.stop();
    }
    
    if (fMilliseconds != 0.0) {
      this.executer = new PeriodicalExecuter(this.send.bind(this), fMilliseconds);
    }
  }
}

</script>

<h2>Analyse de performance du serveur</h2>

<table class="tbl">
  <tr>
	<th>Module</th>
	<th>{{tr}}Action{{/tr}}</th>
	<th>Fréquences</th>
	<th>{{tr}}Action{{/tr}}</th>
	<th>Requêtes</th>
	<th>Réponses</th>
	<th>Dernière durée</th>
	<th>Durée moyenne</th>
  </tr>
  <tr>
    <td>{{tr}}module-{{$module}}-court{{/tr}}</td>
    <td>{{tr}}mod-{{$module}}-tab-{{$action}}{{/tr}}</td>
    <td>
      <select name="frequency" onchange="Benchmark.sendEvery(this.value)">
        <option value="0">&mdash; Arrêt</option>
        <option value="3600">1 heure</option>
        <option value="900">15 minutes</option>
        <option value="240">4 minutes</option>
        <option value="60">1 minute</option>
        <option value="15">15 secondes</option>
        <option value="4">4 secondes</option>
        <option value="1">1 seconde</option>
        <option value="0.25">250 millisecondes</option>
        <option value="0.10">100 millisecondes</option>
      </select>
    </td>
    <td>
      <button class="tick" onclick="Benchmark.send()">Send</button>
    </td>
    <td id="requestCount"></td>
    <td id="responseCount"></td>
    <td id="lastDuration"></td>
    <td id="averageDuration"></td>
  </tr>
</table>

<div id="response" style="display:none"></div>