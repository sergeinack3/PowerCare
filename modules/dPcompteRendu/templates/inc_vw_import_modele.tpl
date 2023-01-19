{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>{{tr var1=$owner}}CCompteRendu-import_for{{/tr}}</h2>

<form name="editImport" method="post" enctype="multipart/form-data"
      action="?m=compteRendu&a=importModeles&dialog=1">
  <input type="hidden" name="owner_guid" value="{{$owner->_guid}}" />

  <div>
    <select name="object_class">
      <option value="">{{tr}}CCompteRendu-import-Default context{{/tr}}</option>

      {{foreach from=$classes key=_class item=_class_view}}
          <option value="{{$_class}}">{{tr}}{{$_class_view}}{{/tr}}</option>
      {{/foreach}}
    </select>
  </div>

  <div style="margin-top: 10px;">
    <input type="file" name="datafile" size="40" />
  </div>

  <div style="text-align: center;">
    <button class="tick">{{tr}}Import{{/tr}}</button>
  </div>
</form>
