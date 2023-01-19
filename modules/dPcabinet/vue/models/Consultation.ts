/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
import OxObject from "@/core/models/OxObject"
import Patient from "@modules/dPpatients/vue/models/Patient"
import { OxAttr } from "@/core/types/OxObjectTypes"
import { OxDate } from "oxify"
import FactureCabinet from "@modules/dPfacturation/vue/models/FactureCabinet"
import Mediuser from "@modules/mediusers/vue/Models/Mediuser"

export default class Consultation extends OxObject {
    public static RELATION_PATIENT = "patient"
    public static RELATION_FACTURE_CABINET = "factureCabinet"
    public static RELATION_MEDIUSER = "mediuser"

    public static UNPAID_SUM_PARAMETER = "unpaid_sum"
    public static COUNT_DOC_PARAMETER = "count_docs"

    protected _relationsTypes = {
        patient: Patient,
        factureCabinet: FactureCabinet,
        mediuser: Mediuser
    }

    constructor () {
        super()
        this.type = "consultation"
    }

    get premiere (): OxAttr<boolean> {
        return super.get("premiere")
    }

    get chrono (): OxAttr<string> {
        return super.get("chrono")
    }

    set chrono (chrono: OxAttr<string>) {
        super.set("chrono", chrono)
    }

    get annule (): OxAttr<boolean> {
        return super.get("annule")
    }

    set annule (annule: OxAttr<boolean>) {
        super.set("annule", annule)
    }

    get heure (): OxAttr<string> {
        const date = new Date(OxDate.getYMD() + " " + super.get("heure"))
        return OxDate.getHm(date)
    }

    get patient (): Patient | null {
        return this.loadForwardRelation<Patient>(Consultation.RELATION_PATIENT)
    }

    set patient (patient: Patient | null) {
        this.setForwardRelation<Patient>(Consultation.RELATION_PATIENT, patient)
    }

    get factureCabinet (): FactureCabinet | null {
        return this.loadForwardRelation<FactureCabinet>(Consultation.RELATION_FACTURE_CABINET)
    }

    set factureCabinet (facture: FactureCabinet | null) {
        this.setForwardRelation(Consultation.RELATION_FACTURE_CABINET, facture)
    }

    get praticien (): Mediuser | null {
        return this.loadForwardRelation<Mediuser>(Consultation.RELATION_MEDIUSER)
    }

    get motif (): OxAttr<string> {
        return super.get("motif")
    }

    set motif (motif: OxAttr<string>) {
        super.set("motif", motif)
    }

    get unpaidSum (): OxAttr<number> {
        return super.get("unpaid_consultations_sum")
    }

    set unpaidSum (sum: OxAttr<number>) {
        super.set("unpaid_consultations_sum", sum)
    }

    get docCount (): number {
        return this.fileCount + this.reportCount
    }

    get reportCount (): number {
        return super.get("report_count") ?? 0
    }

    set reportCount (count: number) {
        super.set("report_count", count ?? 0)
    }

    get fileCount (): number {
        return super.get("file_count") ?? 0
    }

    set fileCount (count: number) {
        super.set("file_count", count ?? 0)
    }

    get prescCount (): number {
        return super.get("presc_count") ?? 0
    }

    set prescCount (count: number) {
        super.set("presc_count", count ?? 0)
    }

    get formCount (): number {
        return super.get("form_count") ?? 0
    }

    set formCount (count: number) {
        super.set("form_count", count ?? 0)
    }

    get arrivee (): OxAttr<string> {
        return super.get("arrivee")
    }

    get patientId (): OxAttr<number> {
        return super.get("patient_id")
    }

    set patientId (patientId: OxAttr<number>) {
        super.set("patient_id", patientId)
    }

    get typeConsultation (): OxAttr<string> {
        return super.get("type_consultation")
    }

    set typeConsultation (type: OxAttr<string>) {
        super.set("type_consultation", type)
    }

    get remarques (): OxAttr<string> {
        return super.get("rques")
    }

    set remarques (type: OxAttr<string>) {
        super.set("rques", type)
    }

    get guid (): string {
        return "CConsultation-" + super.id
    }
}
