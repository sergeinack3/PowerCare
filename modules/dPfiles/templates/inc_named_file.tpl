{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=mode       value=read}}
{{mb_default var=size       value=120}}
{{mb_default var=bigsize    value=240}}
{{mb_default var=border     value="aaa"}}
{{mb_default var=background value="eee"}}
{{mb_default var=default    value="unknown.png"}}

{{assign var=file value=""}} 
{{if (array_key_exists($name, $object->_ref_named_files))}}
  {{assign var=file value=$object->_ref_named_files.$name}}
{{/if}}

{{if "cerfa"|module_active && "cerfa General use_cerfa"|gconf && ($name|strpos:"signature" !== false)}}
  <div class="small-warning">
    {{tr}}Cerfa-msg-Be careful if you use Cerfas with your signature Please have a maximum resolution image of 1280 x 720 pixels{{/tr}}
  </div>
{{/if}}

{{if $file && $file->_id}}
  {{assign var=file_id value=$file->_id}}
  {{assign var=src  value="?m=files&raw=thumbnail&document_guid=`$file->_class`-`$file->_id`&profile=large&crop=1"}}
{{else}}
  {{assign var=src value="images/pictures/$default"}}
{{/if}}

<div id="{{$object->_guid}}-{{$name}}">

<img 
  src="{{$src}}" 
  style="width: {{$size}}px; height: {{$size}}px; border: 2px solid #{{$border}}; background: #{{$background}};" 
  alt="{{$name}}" 
  {{if $file && $file->_id}} 
  onmouseover="ObjectTooltip.createDOM(this, 'tooltip-named-file-{{$file->_id}}')"
  {{/if}} 
/>
     
{{if $file && $file->_id}}     
<div id="tooltip-named-file-{{$file->_id}}" style="display: none;">
  <img 
    src="{{$src}}"
    style="border: 2px solid {{$border}}; background: {{$background}}" alt="Identité" 
  />
</div>
{{/if}}

{{if $mode == "edit"}}
<script>
NamedFile = {
  init: function(object_guid, name, size) {
    this.object_guid = object_guid;
    this.name        = name;
    this.size        = size;
  },
  
  remove: function(object_guid, name, file_id, file_view, size) {
    this.init(object_guid, name, size);
    this.file_id = file_id;
    
    var options = {
      typeName: 'le fichier',
      objName: file_view
    };
  
    var ajax = {
      onComplete: NamedFile.refresh.bind(NamedFile)
    };
    
    var name = 'delete-named-file-'+file_id;
    var form = DOM.form({method: "post", action: '?', name: name},
      DOM.input({type: 'hidden', name: 'm'      , value: 'files'      }),
      DOM.input({type: 'hidden', name: 'dosql'  , value: 'do_file_aed'}),
      DOM.input({type: 'hidden', name: 'del'    , value: '1'          }),
      DOM.input({type: 'hidden', name: 'file_id', value: file_id      })
    );
    
    
    return confirmDeletion(form, options, ajax);
  },
  
  refresh: function() {
    var url = new Url('files', 'vw_named_file');
    url.addParam('object_guid', this.object_guid);
    url.addParam('name', this.name);
    url.addParam('size', this.size);
    url.addParam('mode', 'edit');
    url.requestUpdate(this.object_guid+'-'+this.name);
  },
  
  upload: function(object_guid, name, size) {
    this.init(object_guid, name, size);
    uploadFile(object_guid, null, name, 1);
  }
};

reloadAfterUploadFile = NamedFile.refresh.bind(NamedFile);
</script>

<br />
{{if $file && $file->_id}}
  <button onclick="NamedFile.remove('{{$object->_guid}}', '{{$name}}', '{{$file->_id}}', '{{$file}}', '{{$size}}')" class="trash" type="button">
    {{tr}}Delete{{/tr}}
  </button>

{{else}}
<button type="button" class="search" onclick="NamedFile.upload('{{$object->_guid}}', '{{$name}}', '{{$size}}');">
  {{tr}}Browse{{/tr}}
</button>

{{/if}}

{{/if}}

</div>
