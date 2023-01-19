{{*
 * @package Mediboard\Drawing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<fieldset>
  <legend>{{tr}}CDrawingCategory-legend-Saving draft{{/tr}}</legend>
  <form name="save_file_{{$draw->_id}}" method="post">
    <input type="hidden" name="m" value="drawing" />
    <input type="hidden" name="dosql" value="do_drawfile_aed"/>
    <input type="hidden" name="svg_content" value=""/>
    <input type="hidden" name="export" value="0"/>
    {{mb_field object=$draw field=author_id hidden=1}}
    {{mb_field object=$draw field=file_type hidden=1}}
    {{mb_field object=$draw field=object_class hidden=1}}
    {{mb_field object=$draw field=object_id hidden=1}}
    {{mb_field object=$draw field=file_category_id hidden=1}}
    {{mb_key object=$draw}}
    <table class="form me-no-box-shadow">
      <tr>
        <th class="narrow">{{mb_label object=$draw field=author_id}}</th>
        <td class="me-text-align-left">{{$draw->_ref_author}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$draw field=file_name}}</th>
        <td class="me-text-align-left">{{mb_field object=$draw field=file_name style="width:10em;"}}</td>
      </tr>

      <tr>
        <td class="button" colspan="2">
          <button class="save" onclick="return saveDraw(this.form, 0)">{{tr}}Save{{/tr}}</button>
          {{if $draw->_id}}
            <button class="trash" type="button" onclick="return confirmDeletion(this.form, {ajax:1}, {onComplete:Control.Modal.close});">{{tr}}Delete{{/tr}}</button>
          {{/if}}
        </td>
      </tr>
    </table>
  </form>
</fieldset>

<fieldset>
  <legend>{{tr}}CDrawingCategory-legend-Export{{/tr}}</legend>
  <form name="export_file_{{$draw->_id}}" method="post">
    <input type="hidden" name="m" value="drawing" />
    <input type="hidden" name="dosql" value="do_drawfile_aed"/>
    <input type="hidden" name="svg_content" value=""/>
    <input type="hidden" name="export" value="1"/>
    <input type="hidden" name="remove_draft" value="0"/>
    {{mb_field object=$draw field=author_id hidden=1}}
    {{mb_field object=$draw field=file_type hidden=1}}
    {{mb_field object=$draw field=object_class hidden=1}}
    {{mb_field object=$draw field=object_id hidden=1}}
    {{mb_field object=$draw field=file_category_id hidden=1}}
    {{mb_key object=$draw}}

    <table class="form me-no-box-shadow">
      <tr>
        <th class="narrow">{{mb_label object=$draw field=author_id}}</th>
        <td class="me-text-align-left">{{$draw->_ref_author}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$draw field=file_name}}</th>
        <td class="me-text-align-left">{{mb_field object=$draw field=file_name style="width:10em;"}}</td>
      </tr>
      <tr>
        <th>{{tr}}Category{{/tr}}</th>
        <td class="me-text-align-left">
          <select name="file_category_id">
            <option value="">{{tr}}All{{/tr}}</option>
            {{foreach from=$file_categories item=_cat}}
              <option value="{{$_cat->_id}}" {{if $draw->file_category_id == $_cat->_id}}selected="selected" {{/if}}>{{$_cat}}</option>
            {{/foreach}}
          </select>
        </td>
      </tr>
    </table>
    <button class="upload me-primary" onclick="return saveDraw(this.form, 1)">{{tr}}Export{{/tr}} {{tr}}and{{/tr}} {{tr}}Close{{/tr}}</button>
  </form>
</fieldset>
