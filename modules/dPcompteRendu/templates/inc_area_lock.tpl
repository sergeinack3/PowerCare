{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div id="lock_area" style="display: none;">
    <table class="form">
        <tr>
            <th class="title">
                <button type="button" class="cancel notext" style="float: right;"
                        onclick="$V(getForm('editFrm')._is_locked, 0, false);
                        $V(getForm('editFrm').___is_locked, 0, false);
                        Control.Modal.close();">
                    {{tr}}Cancel{{/tr}}
                </button>
                {{tr}}CCompteRendu-lock_doc{{/tr}}
            </th>
        </tr>
    </table>

    <fieldset>
        <form name="LockDocOwner" method="post">
            <table class="form">
                <tr>
                    <td class="button"
                        {{if $app->_ref_user->_id == $compte_rendu->author_id}}style="display: none;"{{/if}}>
                        <label>
                            <input type="checkbox" name="change_owner"
                                   {{if $app->_ref_user->isPraticien()}}checked{{/if}}/>
                            <strong>{{tr}}CCompteRendu-become_owner{{/tr}}</strong>
                        </label>
                    </td>
                </tr>
                <tr>
                    <td class="text button">
                        <strong>{{tr}}CCompteRendu-lock_for_me{{/tr}}</strong>
                    </td>
                </tr>
                <tr>
                    <td class="button">
                        <button type="button" class="tick"
                                onclick="toggleLock('{{$app->user_id}}', 'LockDocOwner')">{{tr}}Lock{{/tr}}</button>
                    </td>
                </tr>
            </table>
        </form>
    </fieldset>

    <fieldset>
        <form name="LockDocOther" method="post" action="?m=system&a=ajax_password_action"
              onsubmit="return onSubmitFormAjax(this, {useFormAction: true})">
            <input type="hidden" name="user_id" class="notNull"/>
            <input type="hidden" name="form_name" value="LockDocOther"/>
            <input type="hidden" name="callback" value="toggleLock"/>
            <table class="form">
                <tr>
                    <td class="text button" colspan="2">
                        {{tr}}CCompteRendu-lock_for_other{{/tr}}
                    </td>
                </tr>

                <tr>
                    <th>{{tr}}CCompteRendu-user_id{{/tr}}</th>
                    <td>
                        <input type="text" name="_user_view" class="autocomplete"/>
                    </td>
                </tr>

                <tr>
                    <th>
                        <label for="user_password">{{tr}}Password{{/tr}}</label>
                    </th>
                    <td>
                        <input type="password" name="user_password" class="notNull password str"/>
                    </td>
                </tr>

                <tr class="change-owner-container">
                    <td colspan="2" class="button">
                        <label>
                            <input type="checkbox" name="change_owner"
                                   {{if $app->_ref_user->isPraticien()}}checked{{/if}} />
                            {{tr}}CCompteRendu-user_become_owner{{/tr}}
                        </label>
                    </td>
                </tr>

                <tr>
                    <td class="button" colspan="2">
                        <button class="tick singleclick"
                                onclick="return this.form.onsubmit();">{{tr}}Lock{{/tr}}</button>
                    </td>
                </tr>
            </table>
        </form>
    </fieldset>
</div>
