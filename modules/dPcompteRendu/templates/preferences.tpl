{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include template=inc_pref spec=enum var=saveOnPrint values="0|1|2"}}
{{mb_include template=inc_pref spec=enum var=choicepratcab values="prat|cab|group"}}
{{mb_include template=inc_pref spec=enum var=listDefault values="ulli|br|inline"}}
{{mb_include template=inc_pref spec=str  var=listBrPrefix}}
{{mb_include template=inc_pref spec=str  var=listInlineSeparator}}
{{mb_include template=inc_pref spec=bool var=aideTimestamp}}
{{mb_include template=inc_pref spec=bool var=aideOwner}}
{{mb_include template=inc_pref spec=bool var=aideFastMode}}
{{mb_include template=inc_pref spec=bool var=aideAutoComplete}}
{{mb_include template=inc_pref spec=bool var=aideShowOver}}
{{mb_include template=inc_pref spec=bool var=pdf_and_thumbs}}
{{mb_include template=inc_pref spec=bool var=mode_play}}
{{mb_include template=inc_pref spec=bool var=multiple_docs}}
{{mb_include template=inc_pref spec=bool var=auto_capitalize}}
{{mb_include template=inc_pref spec=bool var=auto_replacehelper}}
{{mb_include template=inc_pref spec=bool var=hprim_med_header}}
{{mb_include template=inc_pref spec=bool var=show_old_print}}
{{mb_include template=inc_pref spec=button var=send_document_subject}}
{{mb_include template=inc_pref spec=button var=send_document_body}}
{{mb_include template=inc_pref spec=bool var=multiple_doc_correspondants}}
{{mb_include template=inc_pref spec=bool var=show_creation_date}}
{{mb_include template=inc_pref spec=bool var=secure_signature}}
{{mb_include template=inc_pref spec=bool var=check_to_empty_field}}
{{mb_include template=inc_pref spec=enum var=time_autosave values="0|300|900|1800"}}
{{mb_include template=inc_pref spec=bool var=show_favorites}}
