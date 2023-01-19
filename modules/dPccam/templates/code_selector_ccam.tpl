{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  onSelectAppareil = function(appareil) {
    getForm('filterCode').elements['system'].childElements().each(function(option) {
      if (option.value != '') {
        option.disabled = true;
        option.hide();
      }
    });

    var options = $$('select[name="system"] option[data-apareil="' + appareil + '"]');
    options.each(function(option) {
      option.disabled = false;
      option.show();
    });
  };

  onSelectChapter = function(chapter, level) {
    var next_level = parseInt(level) + 1;
    var select = getForm('filterCode').elements['chapter_' + next_level];
    select.childElements().each(function(option) {
      if (option.value != '') {
        option.remove();
      }
    });
    $V(select, '');

    if (chapter != '') {
      var url = new Url('ccam', 'listChapters');
      url.addParam('parent', chapter);
      url.requestJSON(function (select, datas) {
        for (var data in datas) {
          var text = datas[data]['rank'] + ' - ' + datas[data]['text'];
          select.insert(DOM.option({value: datas[data]['code']}, text));
        }
      }.bind(this, select));
    }
  };

  toggleAdvancedSearch = function() {
    var container = $('advanced_search');
    var icon = $('icon_advanced_search');

    if (container.visible()) {
      container.hide();
      var fields = $$('tbody#advanced_search select').each(function(select) {
        $V(select, '');
      });
      icon.removeClassName('fa-chevron-up');
      icon.addClassName('fa-chevron-down');
      $('simple_search_btn').show();
    }
    else {
      $('simple_search_btn').hide();
      container.show();
      icon.removeClassName('fa-chevron-down');
      icon.addClassName('fa-chevron-up');
    }
  };
</script>

<form name="filterCode" method="get" action="?" onsubmit="return onSubmitFormAjax(this, null, 'code_area');">
  <input type="hidden" name="m" value="dPccam" />
  <input type="hidden" name="a" value="selectorCodeCcam" />
  <input type="hidden" name="only_list" value="1" />
  <input type="hidden" name="chir" value="{{$chir}}" />
  <input type="hidden" name="anesth" value="{{$anesth}}" />
  <input type="hidden" name="object_class" value="{{$object_class}}" />
  <input type="hidden" name="date" value="{{$date}}" />
  <input type="hidden" name="ged" value="{{$ged}}">
  <table class="tbl">
    <tr>
      <th colspan="3">
        Filtre de recherche
      </th>
    </tr>
    <tr>
      <td class="thirdPane">
        Mot-clé : <input type="text" name="_keywords_code" onchange="$V(this.form.tag_id, '', false)"/>
        <button id="simple_search_btn" type="submit" class="search notext"></button>
      </td>
      <td class="thirdPane"></td>
      <td class="thirdPane">
        <label for="tag_id">Tag</label>
        <select name="tag_id" onchange="$V(this.form._keywords_code, ''); this.form.onsubmit()"
          class="taglist" style="width: 18em">
          <option value=""> &mdash; {{tr}}All{{/tr}} </option>
          {{mb_include module=ccam template=inc_favoris_tag_select depth=0}}
        </select>
      </td>
    </tr>
    <tbody id="advanced_search" style="display: none;">
      <tr>
        <td class="thirdPane">
          <label for="access">Voie d'accès</label> :
          <select name="access" style="width: 15em;">
            <option value="">&mdash; Choisir une voie d'accès</option>
            {{foreach from=$access item=_access}}
              <option value="{{$_access.code}}">{{$_access.text}}</option>
            {{/foreach}}
          </select>
        </td>
        <td class="thirdPane">
          <label for="appareil">Appareil</label> :
          <select name="appareil" style="width: 15em;" onchange="onSelectAppareil($V(this));">
            <option value="">&mdash; Choisir un appareil</option>
            {{foreach from=$appareils item=_appareil}}
              <option value="{{$_appareil.code}}">{{$_appareil.text}}</option>
            {{/foreach}}
          </select>
        </td>
        <td class="thirdPane">
          <label for="system">Système</label> :
          <select name="system" style="width: 15em;">
            <option value="">&mdash; Choisir un système</option>
            {{foreach from=$systems item=_system_by_app key=_appareil}}
              {{foreach from=$_system_by_app item=_system}}
                <option value="{{$_system.code}}" data-apareil="{{$_appareil}}" disabled style="display: none;">{{$_system.text}}</option>
              {{/foreach}}
            {{/foreach}}
          </select>
        </td>
      </tr>
      <tr>
        <td colspan="3">
          <span style="width: 49%; text-align: left; display: inline-block; border-right: 1px solid #ddd;">
            <label for="chapter_1">1er chapitre</label> :
            <select name="chapter_1" style="width: 15em;" onchange="onSelectChapter($V(this), 1);">
              <option value="">&mdash; Choisir le 1er chapitre</option>
              {{foreach from=$chapters_1 item=_chapter key=_code}}
                <option value="{{$_code}}">{{$_chapter.rank}} - {{$_chapter.text}}</option>
              {{/foreach}}
            </select>
          </span>

          <span style="width: 49%; text-align: right; display: inline-block;">
            <label for="chapter_2">2ème chapitre</label> :
            <select name="chapter_2" style="width: 15em;" onchange="onSelectChapter($V(this), 2);">
              <option value="">&mdash; Choisir le 2ème chapitre</option>
              {{foreach from=$chapters_2 item=_chapter key=_code}}
                <option value="{{$_code}}">{{$_chapter.rank}} - {{$_chapter.text}}</option>
              {{/foreach}}
            </select>
          </span>
        </td>
      </tr>
      <tr>
        <td colspan="3">
          <span style="width: 49%; text-align: left; display: inline-block; border-right: 1px solid #ddd;">
            <label for="chapter_3">3ème chapitre</label> :
            <select name="chapter_3" style="width: 15em;" onchange="onSelectChapter($V(this), 3);">
              <option value="">&mdash; Choisir le 3ème chapitre</option>
              {{foreach from=$chapters_3 item=_chapter key=_code}}
                <option value="{{$_code}}">{{$_chapter.rank}} - {{$_chapter.text}}</option>
              {{/foreach}}
            </select>
          </span>

          <span style="width: 49%; text-align: right; display: inline-block;">
            <label for="chapter_4">4ème chapitre</label> :
            <select name="chapter_4" style="width: 15em;">
              <option value="">&mdash; Choisir le 4ème chapitre</option>
              {{foreach from=$chapters_4 item=_chapter key=_code}}
                <option value="{{$_code}}">{{$_chapter.rank}} - {{$_chapter.text}}</option>
              {{/foreach}}
            </select>
          </span>
        </td>
      </tr>
      <tr>
        <td class="button" colspan="3">
          <button type="submit" class="search">{{tr}}Search{{/tr}}</button>
        </td>
      </tr>
    </tbody>
    <tr>
      <td colspan="3" onclick="toggleAdvancedSearch();" style="text-align: center; cursor: pointer;" title="Recherche avancée">
        <span>
          <i id="icon_advanced_search" class="fa fa-chevron-down"></i>
        </span>
      </td>
    </tr>
  </table>
</form>

<div id="code_area" style="height: 60%; text-align: left;">
  {{mb_include module=ccam template=inc_code_selector_ccam}}
</div>

