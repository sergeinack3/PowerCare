{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  open_modal = function(id) {
    var element = $(id);
    $$('body')[0].insert(element);
    Modal.open(element, {width: 400, showClose: true} );
  }
</script>

<div id="listObjectsClass">
  <table class="tbl" class="object_class_{{$mod_name}}">
    <tr>
      <th class="title" colspan="2">{{tr}}CPermObject-Type of object in the module|pl{{/tr}} {{tr}}module-{{$mod_name}}-court{{/tr}}</th>
    </tr>
    {{if isset($module_classes.$mod_name|smarty:nodefaults)}}
      {{foreach from=$module_classes.$mod_name item=_class}}
        <tr>
          <td onmouseover="ObjectTooltip.createDOM(this, 'object_class_trad_{{$_class}}');">
            <i class="" aria-hidden="true">  {{tr}}CPermObject-object_class{{/tr}} -</i>
            <strong>{{tr}}{{$_class}}{{/tr}}</strong>
          </td>
          <td class="narrow">
            <button class="add notext" onclick="open_modal('save_object_new-{{$_class}}');">{{tr}}Add{{/tr}}</button>
            <div id="save_object_new-{{$_class}}" style="display: none;">
              {{mb_script module="system" script="object_selector"}}

              {{unique_id var=object_class_uid}}

              <form name="editObjectClass{{$_class}}_{{$object_class_uid}}" method="post"
                    onsubmit="return onSubmitFormAjax(this, function() { Control.Modal.close(); Control.Modal.close(); LoadListExistingRights(); });">
                {{mb_class object=$permObject}}
                {{mb_key object=$permObject}}
                <input type="hidden" name="user_id" value="{{$user_id}}" />
                <input type="hidden" name="perm_object_id" value="" />

                <table class="form">
                  <tr>
                    <th class="title" colspan="2">{{tr}}CPermObject-object_class{{/tr}} - {{tr}}{{$_class}}{{/tr}}</th>
                  </tr>
                  <tr>
                    <th>Objet particulier</th>
                    <td class="button readonly" style="text-align: left;">
                      <input type="text" name="_object_view" value="" readonly="readonly" />
                      <input type="hidden" name="object_id" value="" />
                      <input type="hidden" name="object_class" value="{{$_class}}" />
                      <button type="button" class="search" onclick="ObjectSelector.init{{$_class}}();">
                        Chercher un objet
                      </button>
                      <script>
                        ObjectSelector.init{{$_class}} = function(){
                          this.sForm     = "editObjectClass{{$_class}}_{{$object_class_uid}}";
                          this.sId       = "object_id";
                          this.sView     = "_object_view";
                          this.sClass    = "object_class";
                          this.onlyclass = "false";
                          this.pop();
                        }
                      </script>
                    </td>
                  </tr>
                  <tr>
                    <th>{{mb_label class=CPermModule field=permission}}</th>
                    <td>
                      {{mb_field object=$permObject field=permission}}
                    </td>
                  </tr>
                  <tr>
                    <th></th>
                    <td>
                      <button class="new" type="button" onclick="this.form.onsubmit();">{{tr}}Add{{/tr}}</button>
                    </td>
                  </tr>
                </table>
              </form>
            </div>
          </td>
        </tr>
        <div id="object_class_trad_{{$_class}}" style="display: none;">
          {{$_class}}
        </div>
      {{/foreach}}
    {{else}}
      <tr>
        <td class="empty" colspan="2">{{tr}}CMbObject.none{{/tr}}</td>
      </tr>
    {{/if}}
  </table>
</div>
