{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<style>
  .upload-progress {
    text-align: center;
    vertical-align: middle;
    line-height: 1;
    width: 220px;
    font-size: 1.5rem;
  }

  .upload-progress:before {
    font-size: 1rem;
    vertical-align: middle;
    content: "";
  }
  .upload-progress[data-value]:before {
    content: attr(data-value) " / " attr(data-total) " (" attr(data-pct) " %)";
  }

  .resumable-browse {
    font-size: 1.5em;
    background: rgba(135, 195, 215, 0.46);
    padding: 0.5em;
    margin: 0.5em;
    border-radius: 5px;
    cursor: move;
  }

  .progress {
    text-align: center;
    font-size: 2em;
  }
</style>

<script>
  require(['modules/dPfiles/javascript/resumable'], function(Resumable) {
    window.r = new Resumable({
      target:'?m=files&raw=upload',
      maxFileSize: {{$uploader->getMaxUploadSize()|json}}
    });

    var browse = $$(".resumable-browse");

    r.assignBrowse(browse);
    r.assignDrop(browse);

    r.on('fileAdded', function(file){
      var info = $("file-info");
      info.insert(DOM.tr({className: "file-row", "data-uniqueIdentifier": file.uniqueIdentifier},
        DOM.td({}, DOM.button({className: "fa fa-ban notext compact"})),
        DOM.td({}, file.fileName),
        DOM.td({}, file.size.toLocaleString()),
        DOM.td({}, file.type),
        DOM.td({}, DOM.progress({
          id: "progress-"+file.uniqueIdentifier,
          className: "upload-progress",
          max: 10000,
          value: 0,
          "data-total": file.size.toLocaleString()
        })),
        DOM.td({id: "msg-"+file.uniqueIdentifier})
      ));
    });

    r.on('fileSuccess', function(file, message){
      var target = $("msg-"+file.uniqueIdentifier);
      target.update(message.replace(/\n/g, '<br />'));
    });
    r.on('fileError', function(file, message){
      var target = $("msg-"+file.uniqueIdentifier);
      target.update(message.replace(/\n/g, '<br />'));
    });

    r.on('fileProgress', function(file){
      var p = $("progress-"+file.uniqueIdentifier);
      var progress = file.progress();

      p.value = Math.round(progress * 10000);
      p.set("value", Math.round(progress * file.size).toLocaleString());
      p.set("pct", Math.round(progress * 10000) / 100);
    });

    r.on('progress', function(){
      var p = $("progress-bar");
      var progress = r.progress();
      var size = r.getSize();

      p.value = Math.round(progress * 10000);
      p.set("value", Math.round(progress * size).toLocaleString());
      p.set("total", size.toLocaleString());
      p.set("pct", Math.round(progress * 10000) / 100);
    });

    r.on('complete', function(){
      $("upload-success").show().update("Upload terminé !");
      r.cancel();
    });
  });

  Main.add(function(){
    document.on("click", ".file-row button.fa-ban", function(event){
      var element = Event.element(event);
      var row = element.up("tr");
      var file = r.files.find(function(file){
        return file.uniqueIdentifier == row.get("uniqueIdentifier");
      });

      file.cancel();
      row.remove();
    });
  });

  Uploader = {
    start: function() {
      r.upload();

      $$(".uploader-pause, .uploader-cancel").invoke("enable");
      $$(".uploader-start").invoke("disable");

      return false;
    },
    pause: function() {
      r.pause();

      $$(".uploader-start, .uploader-cancel").invoke("enable");
      $$(".uploader-pause").invoke("disable");

      return false;
    },
    cancel: function() {
      r.cancel();
      $('file-info').update('');
      $('upload-success').update('').hide();

      $$(".uploader-pause, .uploader-cancel").invoke("disable");
      $$(".uploader-start").invoke("enable");

      return false;
    },
    removeFile: function(button, filename) {
      var url = new Url("files", "do_delete_uploaded_file", "dosql");
      url.addParam("filename", filename);
      url.requestJSON(function(success){
        if (success) {
          button.up('tr').remove();
        }
      }, {
        method: "post"
      });
    },
    removeTemp: function(button, dirname) {
      var url = new Url("files", "do_delete_uploaded_file", "dosql");
      url.addParam("filename", dirname);
      url.addParam("temp", 1);
      url.requestJSON(function(success){
        if (success) {
          button.up('tr').remove();
        }
      }, {
        method: "post"
      });
    }
  }
</script>

<table class="main layout">
  <tr>
    <td style="min-width: 500px; width: 40%;">
      <table class="main tbl">
        <tr>
          <th class="title" colspan="5">Fichiers uploadés</th>
        </tr>

        <tr>
          <th class="narrow"></th>
          <th>{{mb_label class=CFile field=file_name}}</th>
          <th>{{mb_label class=CFile field=_file_path}}</th>
          <th class="narrow">{{mb_label class=CFile field=doc_size}}</th>
          <th class="narrow">{{mb_label class=CFile field=file_date}}</th>
        </tr>

        {{foreach from=$uploaded_files item=_file}}
          <tr>
            <td>
              <button class="fas notext compact fa-trash-alt" onclick='Uploader.removeFile(this, {{$_file.name|json}})'></button>
            </td>
            <td>{{$_file.name}}</td>

            <td class="text compact">{{$_file.path}}</td>

            <td style="text-align: right;" title="{{$_file.size}}">{{$_file.size|decabinary}}</td>
            <td>{{$_file.date}}</td>
          </tr>
          {{foreachelse}}
          <tr>
            <td colspan="5" class="empty">{{tr}}CFile.none{{/tr}}</td>
          </tr>
        {{/foreach}}
      </table>

      <table class="main tbl">
        <tr>
          <th class="title" colspan="4">Fichiers en cours d'upload ou échoués</th>
        </tr>
        <tr>
          <th class="narrow"></th>
          <th>{{tr}}CFile-file_name{{/tr}}</th>
          <th class="narrow">{{tr}}CFile-file_date{{/tr}}</th>
        </tr>

        {{foreach from=$uploaded_temp item=_temp}}
          <tr>
            <td>
              <button class="fas notext compact fa-trash-alt" onclick='Uploader.removeTemp(this, {{$_temp.name|json}})'></button>
            </td>
            <td>{{$_temp.name}}</td>
            <td>{{$_temp.date}}</td>
          </tr>
          {{foreachelse}}
          <tr>
            <td colspan="4" class="empty">{{tr}}CFile.none{{/tr}}</td>
          </tr>
        {{/foreach}}
      </table>
    </td>
    <td>
      <div class="resumable-browse">
        Déposer ou cliquer ici pour uploader des fichiers
      </div>

      <div class="progress">
        <button class="fa notext fa-play uploader-start" onclick="return Uploader.start()"></button>
        <button class="fa notext fa-pause uploader-pause" onclick="return Uploader.pause()" disabled></button>
        <button class="fa notext fa-ban uploader-cancel" onclick="return Uploader.cancel()" disabled></button>
        <progress id="progress-bar" value="0" max="10000" class="upload-progress"></progress>
      </div>

      <table class="main tbl">
        <thead>
        <tr>
          <th class="title" colspan="6">
            Upload
          </th>
        </tr>
        <tr>
          <th class="narrow"></th>
          <th>Fichier</th>
          <th>Taille</th>
          <th>Type</th>
          <th class="narrow"></th>
          <th>Status</th>
        </tr>
        </thead>
        <tbody id="file-info"></tbody>
      </table>

      <div id="upload-success" class="small-success" style="display: none;"></div>
    </td>
  </tr>
</table>

