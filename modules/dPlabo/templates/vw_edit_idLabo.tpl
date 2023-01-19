{{*
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main">
  <tr>
    <td class="halfPane">
      <table class="tbl">
        <tr>
          <th colspan="3" class="title">
            Identifiants labo
          </th>
        </tr>
        <tr>
          <th class="category">Praticiens</th>
          <th class="category">Code4</th>
          <th class="category">Code9</th>
        </tr>
        {{foreach from=$listPraticiens item="praticien"}}
        <tr>
          <td>{{$praticien.prat}}</td>
          <td>
            <form name="editCode4-{{$praticien.prat->_id}}" method="post" onsubmit="return onSubmitFormAjax(this);">
              <input type="hidden" name="m" value="sante400" />
              <input type="hidden" name="dosql" value="do_idsante400_aed" />
              {{mb_key object=$praticien.code4}}
              <input type="hidden" name="object_class" value="CMediusers" />
              <input type="hidden" name="object_id" value="{{$praticien.prat->_id}}" />
              <input type="hidden" name="tag" value="labo code4" />
              <input type="hidden" name="last_update" value="{{$today}}" /> 
              {{mb_field object=$praticien.code4 field="id400"}}
              <button class="notext submit">{{tr}}Submit{{/tr}}</button>
            </form>
          </td>
          <td>
            <form name="editCode9-{{$praticien.prat->_id}}" method="post" onsubmit="return onSubmitFormAjax(this);">
              <input type="hidden" name="m" value="sante400" />
              <input type="hidden" name="dosql" value="do_idsante400_aed" />
              {{mb_key object=$praticien.code9}}
              <input type="hidden" name="object_class" value="CMediusers" />
              <input type="hidden" name="object_id" value="{{$praticien.prat->_id}}" />
              <input type="hidden" name="tag" value="labo code9" />
              <input type="hidden" name="last_update" value="{{$today}}" /> 
              {{mb_field object=$praticien.code9 field="id400"}}
              <button class="notext submit">{{tr}}Submit{{/tr}}</button>
            </form>
          </td>
        </tr>
        {{/foreach}}
      </table>
    </td>
  </tr>
</table>