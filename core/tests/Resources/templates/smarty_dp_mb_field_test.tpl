{{*
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div id="mb-field-str">{{mb_field object=$user field='user_username' canNull=true}}</div>

<div id="mb-field-num">{{mb_field object=$user field='user_type' canNull=false}}</div>

<div id="mb-field-enum-select">{{mb_field object=$user field='user_sexe' alphabet=true prefix='test'}}</div>

<div id="mb-field-enum-radio">{{mb_field object=$user field='user_sexe' typeEnum='radio' alphabet=true}}</div>

<div id="mb-field-bool-radio">{{mb_field object=$user field='template' separator='|'}}</div>

<div id="mb-field-bool-checkbox">{{mb_field object=$user field='template' typeEnum='checkbox'}}</div>

<div id="mb-field-bool-select">{{mb_field object=$user field='template' typeEnum='select'}}</div>

<div id="mb-field-set-select">{{mb_field object=$mediuser field='_ldap_bound' typeEnum='select'}}</div>

<div id="mb-field-set-checkbox">{{mb_field object=$mediuser field='_ldap_bound'}}</div>

<div id="mb-field-password">{{mb_field object=$mediuser field='_user_password'}}</div>
