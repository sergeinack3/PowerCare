{{*
 * @package Mediboard\Lpp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    selectChapter = function (select) {
        var chapter_id = $V(select);
        if (chapter_id) {
            new Url('lpp', 'getDescendantChapters')
                .addParam('parent_id', chapter_id)
                .requestJSON(function (data) {
                    /* We remove the select of equal rank or superior */
                    for (var i = data.level; i <= 10; i++) {
                        var select = $('select_chapter_' + i);

                        if (!select) {
                            break;
                        }

                        select.remove();
                    }

                    if (data.chapters.length) {
                        var div = DOM.div({id: 'select_chapter_' + data.level});
                        var select = DOM.select({
                            name:         'chapter_' + data.level,
                            id:           'searchLPPCode_chapter_' + data.level,
                            onchange:     "selectChapter(this);",
                            'data-level': data.level
                        });
                        select.insert(DOM.option({value: ''}, '&mdash; ' + $T('CLPPChapter-action-select')));
                        data.chapters.each(function (chapter) {
                            select.insert(DOM.option({value: chapter.id}, chapter.view));
                        });
                        div.insert(select);
                        $('chapters').insert(div);
                    }
                });
        } else {
            /* We remove the select of equal rank or superior */
            for (var i = parseInt(select.readAttribute('data-level')) + 1; i <= 10; i++) {
                var select = $('select_chapter_' + i);

                if (!select) {
                    break;
                }

                select.remove();
            }
        }
    };

    searchCode = function (form, start) {
        var url = new Url('lpp', 'searchLppCodes');
        url.addParam('code', $V(form.code));
        url.addParam('text', $V(form.text));

        /* We remove the select of equal rank or superior */
        for (var i = 10; i >= 1; i--) {
            var select = $('searchLPPCode_chapter_' + i);

            if (select && $V(select)) {
                var chapter_id = $V(select);
                break;
            }
        }

        if (start) {
            url.addParam('start', start);
        }

        url.addParam('chapter_id', chapter_id);
        url.requestUpdate('results');
    };

    changePage = function (start, limit) {
        searchCode(getForm('searchLPPCode'), start);
    };

    displayCode = function (code) {
        new Url('lpp', 'viewCode')
            .addParam('code', code)
            .requestModal();
    }
</script>

<form name="searchLPPCode" method="GET" action="?" onsubmit="searchCode(this, 0);">
    <table class="form">
        <tr>
            <th class="title" colspan="4">{{tr}}filter-criteria{{/tr}}</th>
        </tr>
        <tr>
            <th style="width: 20%;">
                <label for="code" title="{{tr}}CLPPCode-code-desc{{/tr}}">{{tr}}CLPPCode-code{{/tr}}</label>
            </th>
            <td style="width: 40%;">
                <input type="text" name="code" size="7"/>
            </td>
            <th style="width: 20%;">
                <label for="name" title="{{tr}}CLPPCode-name-desc{{/tr}}">{{tr}}CLPPCode-name{{/tr}}</label>
            </th>
            <td style="width: 20%;">
                <input type="text" name="text"/>
            </td>
        </tr>
        <tr>
            <th>
                <label for="chapters" title="{{tr}}CLPPChapter|pl{{/tr}}">{{tr}}CLPPChapter|pl{{/tr}}</label>
            </th>
            <td id="chapters" colspan="3">
                <div id="select_chapter_1">
                    <select data-level="1" name="chapter_1" onchange="selectChapter(this);">
                        <option value="">&mdash; {{tr}}CLPPChapter-action-select{{/tr}}</option>
                        {{foreach from=$chapters item=_chapter}}
                            <option value="{{$_chapter->id}}">{{$_chapter->rank}} - {{$_chapter->name}}</option>
                        {{/foreach}}
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <td class="buttons" style="text-align: center;" colspan="4">
                <button id="search_codes" type="button" class="search"
                        onclick="this.form.onsubmit();">{{tr}}Search{{/tr}}</button>
            </td>
        </tr>
    </table>
</form>

<div id="results" class="me-padding-0">
    {{mb_include module=lpp template=inc_search_results}}
</div>
