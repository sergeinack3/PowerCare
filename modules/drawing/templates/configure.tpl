{{*
 * @package Mediboard\Drawing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.create('tabs-configure', true);
    Configuration.edit(
      'drawing',
      ['CGroups'],
      $('CConfigEtab')
    );
  });
</script>

<ul id="tabs-configure" class="control_tabs">
  <li><a href="#CConfigEtab"    >{{tr}}CConfigEtab{{/tr}}</a></li>
  <li><a href="#import_pack"        >{{tr}}CDrawingCategory-Import pack{{/tr}}</a></li>
</ul>


<div id="CConfigEtab" style="display: none"></div>

<div id="import_pack" style="display: none">
  <form name="import_pack" action="?m={{$m}}&{{$actionType}}=configure" method="post" enctype="multipart/form-data">
    <input type="hidden" name="m" value="drawing" />
    <input type="hidden" name="dosql" value="do_import_image_pack" />
    <input type="hidden" name="MAX_FILE_SIZE" value="4096000" />

    <table class="form">
      <tr>
        <th>{{tr}}File{{/tr}} (zip)</th>
        <td>
          <input type="file" name="zip" />
        </td>
      </tr>
      <tr>
        <th>{{tr}}CDrawingCategory-Target category{{/tr}}</th>
        <td>
          <select name="category" style="float: left;">
            <option value="">&mdash; {{tr}}CDrawingCategory-Automatic{{/tr}}</option>
            {{foreach from=$cats item=_cat}}
              <option value="{{$_cat->name}}">{{$_cat}}</option>
            {{/foreach}}
          </select>
          <div class="info" style="padding-left:20px;">{{tr}}CDrawingCategory-msg-If automatic, the software will add the categories based on the folder names{{/tr}}</div>
        </td>
      </tr>
      <tr>
        <td colspan="2" class="button">
          <button class="upload" type="submit">{{tr}}Upload{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>
</div>