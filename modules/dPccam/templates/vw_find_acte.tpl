{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPccam script=CCodageCCAM ajax=true}}

<div id="multiple" class="reseach">
  <form name="multiple_form" method="get" onsubmit="return CCodageCCAM.submitFunction(this);">
    <input type="hidden" name="m" value="dPccam" />
    <input type="hidden" name="result_chap1" value="" />
    <input type="hidden" name="result_chap2" value="" />
    <input type="hidden" name="result_chap3" value="" />
    <input type="hidden" name="result_chap4" value="" />
    <input type="hidden" name="date_demandee" value="" />
    <input type="hidden" name="page" value="{{$page}}" onchange="this.form.onsubmit()" />
    <table class="main layout">
      <tr>
        <td class="separator expand" onclick="MbObject.toggleColumn(this, $(this).next())"></td>
        <td>
          <table class="main form">
            <tr>
              <th style="width: 25%;">Code :</th>
              <td><input name="code" /></td>
              <th>1er niveau</th>
              <td>
                <select name="chap1" id="chap1" style="width:50%;"
                        onchange="CCodageCCAM.remiseAZeroSelect(this);CCodageCCAM.associeFonction(this);"
                        data-index="1">
                  <option>Choisir le 1er niveau du chapitre</option>
                  {{foreach from=$listChap1 item=curr_chap key=key_chap}}
                  <option value="{{$curr_chap.rank}}" data-code-pere="{{$key_chap}}">
                    {{$curr_chap.rank}} - {{$curr_chap.text}}
                  </option>
                  {{/foreach}}
                </select>
              </td>
            </tr>
            <tr>
              <th>Mots clés :</th>
              <td><input name="keywords" id="keywords" /></td>
              <th>2ème niveau</th>
              <td>
                <select name="chap2" id="chap2" style="width:50%;"
                        onchange="CCodageCCAM.remiseAZeroSelect(this);CCodageCCAM.associeFonction(this);"
                        data-index="2">
                  <option value="">Choisir le 2ème niveau du chapitre</option>
                </select>
              </td>
            </tr>
            <tr>
              <th colspan="3">3ème niveau</th>
              <td>
                <select name="chap3" id="chap3" style="width:50%;"
                        onchange="CCodageCCAM.remiseAZeroSelect(this);CCodageCCAM.associeFonction(this);"
                        data-index="3">
                  <option value="">Choisir le 3ème niveau du chapitre</option>
                </select>
              </td>
            </tr>
            <tr>
              <th colspan="3">4ème niveau</th>
              <td>
                <select name="chap4" id="chap4" style="width:50%;"
                        onchange="CCodageCCAM.remiseAZeroSelect(this);CCodageCCAM.associeFonction(this);"
                        data-index="4">
                  <option value="">Choisir le 4ème niveau du chapitre</option>
                </select>
              </td>
            </tr>
            <tr>
              <td colspan="4" class="button">
                <button type="submit" class="search" title="{{tr}}Search{{/tr}}">{{tr}}Search{{/tr}}</button>
              </td>
            </tr>
          </table>
      </tr>
    </table>
  </form>
</div>

<div id="result_keyword"></div>

