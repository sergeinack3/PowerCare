/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
import OxObject from "@/core/models/OxObject"
import SamplePerson from "./SamplePerson"
import SampleCategory from "./SampleCategory"
import { tr } from "@/core/utils/OxTranslator"
import SampleCasting from "@modules/sample/vue/models/SampleCasting"
import { OxAttr, OxAttrNullable } from "@/core/types/OxObjectTypes"

export default class SampleMovie extends OxObject {
    constructor () {
        super()
        this.type = "sample_movie"
    }

    protected _relationsTypes = {
        sample_person: SamplePerson,
        sample_category: SampleCategory,
        sample_casting: SampleCasting
    }

    get image (): string | undefined {
        return this.links.cover
    }

    get languages (): string {
        let translator = ""

        if (this.languagesData) {
            const languages = this.languagesData.split("|")

            languages.forEach((language, index) => {
                if (index === 0) {
                    translator += tr("CSampleMovie.languages." + language)
                    return
                }
                translator += ", " + tr("CSampleMovie.languages." + language)
            })
        }
        return translator
    }

    get languagesData (): OxAttrNullable<string> {
        return super.get("languages")
    }

    set languagesData (value: OxAttrNullable<string>) {
        this.set("languages", value)
    }

    get title (): OxAttr<string> {
        return super.get("name")
    }

    set title (value: OxAttr<string>) {
        super.set("name", value)
    }

    get description (): OxAttrNullable<string> {
        return super.get("description")
    }

    set description (value: OxAttrNullable<string>) {
        this.set("description", value)
    }

    get release (): string {
        return new Date(this.attributes.release).toLocaleDateString("fr")
    }

    get releaseData (): OxAttr<string> {
        return super.get("release")
    }

    set releaseData (value: OxAttr<string>) {
        this.set("release", value)
    }

    get duration (): OxAttr<string> {
        return this.get("duration")
    }

    set duration (value: OxAttr<string>) {
        this.set("duration", value)
    }

    get csa (): OxAttrNullable<string> {
        return this.get("csa")
    }

    set csa (value: OxAttrNullable<string>) {
        this.set("csa", value)
    }

    get category (): SampleCategory | null {
        return this.loadForwardRelation<SampleCategory>("category")
    }

    set category (value: SampleCategory | null) {
        this.setForwardRelation("category", value)
    }

    get director (): SamplePerson | null {
        return this.loadForwardRelation<SamplePerson>("director")
    }

    set director (value: SamplePerson | null) {
        this.setForwardRelation("director", value)
    }

    get permEdit (): boolean {
        return this.meta?.permissions?.perm === "edit"
    }

    get releaseYear (): string {
        return new Date(this.attributes.release).getFullYear().toString()
    }

    get castingUrl (): string | undefined {
        return this.links.casting
    }

    get detailLink (): string {
        return this.links.self_legacy ?? ""
    }

    get actors (): SamplePerson[] {
        return this.loadBackwardRelation("actors")
    }

    get casting (): SampleCasting[] {
        return this.loadBackwardRelation("casting")
    }

    public addRelationCastings (castings: SampleCasting[]) {
        castings.forEach((casting) => {
            this.addBackwardRelation("casting", casting)
        })
    }
}
