{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  //Autocomplete
  Main.add(function(){
    var form = getForm("editTranslationO");
    var source = form.elements.source;
    var mbtrad = form.elements.mbtrad;
    var url = new Url("system", "ajax_translation_autocomplete");
    url.addParam("source", $V(source));
    url.autoComplete(source, null, {
      minChars: 3,
      method: "get",
      select: "view",
      dropdown: true,
      afterUpdateElement: function(field, selected){
        if ($V(source) == "") {
          $V(source, selected.get("string"));
          $V(mbtrad, selected.get("locale"));
        }
      }
    });
  });
</script>

  <form method="post" name="editTranslationO" action="?m=system&tab=view_translations" onsubmit="return checkForm(this);">
    <input type="hidden" name="dosql" value="do_translationoverwrite_aed"/>
    {{mb_key object=$translation}}
    {{mb_class object=$translation}}

    <table class="form">
      <tbody>
      <tr>
        <th colspan="2" class="title">
      {{if $translation->_id}}
        {{tr}}CTranslationOverwrite.editof{{/tr}} "{{tr}}{{$translation->source}}{{/tr}}"

        <span style="text-align: right">
          {{mb_include module=system template=inc_object_history object=$translation}}
        </span>
      {{else}}
          {{tr}}CTranslationOverwrite.new{{/tr}}
      {{/if}}
        </th>
      </tr>
      <tr>
        <th>{{mb_label object=$translation field=language}}</th>
        <td>{{mb_field object=$translation field=language typeEnum=radio}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$translation field=source}}</th>
        <td>{{mb_field object=$translation field=source size=50}}</td>
      </tr>
      <tr>
        <th>{{tr}}CTranslationOverwrite-_old_translation{{/tr}}</th>
        <td>
          <textarea name="mbtrad" disabled="disabled">{{$translation->_old_translation}}</textarea>
        </td>
      </tr>
      <tr>
        <th>{{mb_label object=$translation field=translation}}</th>
        <td>{{mb_field object=$translation field=translation}}</td>
      </tr>
      <tr>
        <td class="button" colspan="2">
          {{if $translation->_id}}
            <button type="submit" class="save" >{{tr}}Edit{{/tr}}</button>
            <button type="submit" class="trash" onclick="confirmDeletion(this.form);">{{tr}}Delete{{/tr}}</button>
          {{else}}
            <button type="submit" class="save" >{{tr}}Add{{/tr}}</button>
          {{/if}}
        </td>
      </tr>
      </tbody>
    </table>
  </form>
