{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(function() {
    var files = ['file_20', 'file_50', 'file_80', 'file_100'];
    files.each(function(file) {
      var img = $(file);
      img.on('load', checkPerfs);
    });
  });

  function checkPerfs() {
    var perfs = performance.getEntries();

    for (var i = 0; i < perfs.length; i++) {
      var perf = perfs[i];
      if (perf.name !== this.currentSrc) {
        continue;
      }

      var legend = $(this.id+'_legend');
      legend.innerHTML = 'Size : ' + parseInt(perf.transferSize/1024, 10) + 'Kb';
      legend.innerHTML += '<br/>Temps de génération : ' + parseInt(perf.responseStart - perf.requestStart, 10) + 'ms';
      legend.innerHTML += '<br/>Width : ' + this.clientWidth + 'px<br/>Height : ' + this.clientHeight + 'px';
    }
  }

</script>

<table class="main tbl">
  <tr>
    <td>
      <fieldset>
        <legend>20%</legend>
        <legend id="file_20_legend"></legend>
        {{thumbnail id=file_20 document=$file profile=large default_size=1 quality=low}}
      </fieldset>
    </td>
    <td>
      <fieldset>
        <legend>50%</legend>
        <legend id="file_50_legend"></legend>
        {{thumbnail id=file_50 document=$file profile=large default_size=1 quality=medium}}
      </fieldset>
    </td>
  </tr>
  <tr>
    <td>
      <fieldset>
        <legend>80%</legend>
        <legend id="file_80_legend"></legend>
        {{thumbnail id=file_80 document=$file profile=large default_size=1 quality=high}}
      </fieldset>
    </td>
    <td>
      <fieldset>
        <legend>100%</legend>
        <legend id="file_100_legend"></legend>
        {{thumbnail id=file_100 document=$file profile=large default_size=1 quality=full}}
      </fieldset>
    </td>
  </tr>
</table>