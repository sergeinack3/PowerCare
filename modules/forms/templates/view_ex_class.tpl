{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(function () {
    Control.Tabs.create("ex-class-tabs", true, {
      afterChange: function (container) {
        if (container.id == "tag-tree") {
          MbObject.list("{{$object_class}}", null, '{{$g}}');
        }
      }
    });

    var hash = Url.parse().fragment;
    if (hash) {
      var match = hash.match(/edit\.(.+)/);
      if (match) {
        MbObject.edit(match[1]);
        return;
      }
    }

    MbObject.edit("{{$object_class}}-0");
  });
</script>

<table class="main layout">
  <tr>
    <td class="me-padding-left-8" style="width: 250px; {{if $hide_tree}} display: none; {{/if}}">
      <ul class="control_tabs small" id="ex-class-tabs">
        <li><a href="#tag-tree" class="small">Par tag</a></li>
        <li><a href="#ex-class-categories">Par catégorie</a></li>
      </ul>

      <div id="tag-tree" style="display: none;" class="me-padding-0"></div>

      <div id="ex-class-categories" style="display: none;">
        <table class="main tbl treegrid me-no-box-shadow me-no-align me-no-border" style="width: 250px;">
          {{foreach from=$categories item=_category}}
            <tbody data-category_id="{{$_category->_id}}">
            <tr>
              <td class="text">
                <a href="#1" style="font-weight: bold; border-color: #{{$_category->color}}" class="tree-folding"
                   title="{{$_category->description}}"
                   onclick="var tb = this.up('tbody'); tb.toggleClassName('opened'); tb.next('tbody').toggle(); return false;">
                  {{if $_category->_id}}
                    {{$_category}}
                  {{else}}
                    &mdash; {{tr}}CExClassCategory.none{{/tr}}
                  {{/if}}

                  (<small>{{$_category->_ref_ex_classes|@count}}</small>)
                </a>
              </td>
            </tr>
            </tbody>

            <tbody style="display: none;">
            {{foreach from=$_category->_ref_ex_classes item=_ex_class}}
              <tr>
                <td class="text" style="padding-left: 1em;">
                  <a href="#edit.{{$_ex_class->_guid}}"
                     onclick=" this.up('tr').addUniqueClassName('selected', 'table'); MbObject.edit(this)"
                     data-object_guid="{{$_ex_class->_guid}}">
                    <span onmouseover="ObjectTooltip.createEx(this, '{{$_ex_class->_guid}}');">
                      {{$_ex_class}}
                    </span>
                  </a>
                </td>
              </tr>
            {{/foreach}}
            </tbody>
          {{/foreach}}
        </table>
      </div>
    </td>
    {{if !$hide_tree}}
      <td class="separator" onclick="MbObject.toggleColumn(this, $(this).previous())"></td>
    {{/if}}
    <td id="object-editor">&nbsp;</td>
  </tr>
</table>
