/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

BandwidthTest = {
  start: 0,
  end: 0,
  binFileContent: "",
  binFileUrl: "",
  binEmptyFileUrl: "",
  downloadLimit: 512,
  uploadLimit: 80,
  launchTest: function() {
    var url = Url.parse();
    var urlPath = url.scheme+"://"+location.hostname+url.directory+"tmp/bandwidth_test/";

    BandwidthTest.binFileUrl      = urlPath+"big.bin";
    BandwidthTest.binEmptyFileUrl = urlPath+"empty.bin";

    $('dlspeedref').down('span').update(BandwidthTest.downloadLimit);
    $('ulspeedref').down('span').update(BandwidthTest.uploadLimit);

    // Prepare BIN files (the big one and the empty one)
    var url = new Url("system", "bandwidth_big_file");
    url.addParam("size", 1024); // in KB
    url.requestJSON(BandwidthTest.download);
  },
  download: function() {
    BandwidthTest.start = new Date().getTime();

    var options = {
      method: "get",
      parameters: $H({id: BandwidthTest.start}).toQueryString(),
      asynchronous: true,
      evalScripts: false,
      onSuccess: function(transport){
        BandwidthTest.end = new Date().getTime();

        BandwidthTest.binfile = transport.responseText;

        var diff = (BandwidthTest.end - BandwidthTest.start) / 1000;
        var speed = (BandwidthTest.binfile.length / diff) / (1024 / 8);
        speed = Math.round(speed*100)/100;

        $('dlspeed').down('.bar').setStyle({width: (speed/BandwidthTest.downloadLimit)*100+'%'});
        $('dlspeed').down('span').update(speed);

        BandwidthTest.upload();
      }
    };

    new Ajax.Request(BandwidthTest.binFileUrl, options);
  },
  upload: function() {
    BandwidthTest.start = new Date().getTime();

    var options = {
      method: "post",
      postBody: BandwidthTest.binfile,
      asynchronous: true,
      evalScripts: false,
      contentType: "application/octet-stream",
      onSuccess: function(){
        BandwidthTest.end = new Date().getTime();

        var diff = (BandwidthTest.end - BandwidthTest.start) / 1000;
        var speed = (BandwidthTest.binfile.length / diff) / (1024 / 8);
        speed = Math.round(speed*100)/100;

        $('ulspeed').down('.bar').setStyle({width: (speed/BandwidthTest.uploadLimit)*100+'%'});
        $('ulspeed').down('span').update(speed);
      }
    };

    new Ajax.Request(BandwidthTest.binEmptyFileUrl, options);
  }
}
