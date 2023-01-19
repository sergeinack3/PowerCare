/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
import OxObject from "@/core/models/OxObject"
import SampleNationality from "./SampleNationality"
import { tr } from "@/core/utils/OxTranslator"
import { OxAttr, OxAttrNullable } from "@/core/types/OxObjectTypes"

export default class SamplePerson extends OxObject {
    constructor () {
        super()
        this.type = "sample_person"
    }

    protected _relationsTypes = {
        sample_nationality: SampleNationality
    }

    get firstName (): OxAttr<string> {
        return super.get("first_name")
    }

    set firstName (value: OxAttr<string>) {
        super.set("first_name", value)
    }

    get lastName (): OxAttr<string> {
        return super.get("last_name")
    }

    set lastName (value: OxAttr<string>) {
        super.set("last_name", value)
    }

    get fullName (): string {
        return this.firstName + " " + this.lastName
    }

    get isDirector (): OxAttrNullable<boolean> {
        return super.get("is_director")
    }

    set isDirector (value: OxAttrNullable<boolean>) {
        super.set("is_director", value)
    }

    get isDirectorString (): string {
        return this.isDirector ? tr("CSamplePerson.is_director.y") : tr("CSamplePerson.is_director.n")
    }

    get birthdate (): string {
        return this.birthdateData ? new Date(this.birthdateData).toLocaleDateString("fr") : ""
    }

    get birthdateData (): OxAttrNullable<string> {
        return super.get("birthdate")
    }

    set birthdateData (value: OxAttrNullable<string>) {
        super.set("birthdate", value)
    }

    get sex (): OxAttrNullable<string> {
        return super.get("sex")
    }

    set sex (value: OxAttrNullable<string>) {
        super.set("sex", value)
    }

    get sexIcon (): string | undefined {
        if (!this.sex) {
            return undefined
        }

        return this.sex === "m" ? "male" : "female"
    }

    get activityStart (): number | string {
        return this.activityStartData ? new Date(this.activityStartData).getFullYear() : ""
    }

    get activityStartData (): OxAttrNullable<string> {
        return super.get("activity_start")
    }

    set activityStartData (value: OxAttrNullable<string>) {
        super.set("activity_start", value)
    }

    get profilePicture (): string | undefined {
        return this.links.profile_picture
    }

    get nationality (): SampleNationality | null {
        return this.loadForwardRelation<SampleNationality>("nationality")
    }

    set nationality (value: SampleNationality | null) {
        this.setForwardRelation("nationality", value)
    }
}
