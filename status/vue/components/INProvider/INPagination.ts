/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import INProvider from "./INProvider"

export default class INPagination {
  private provider!: INProvider

  private links: {next: string, first: string, last: string, previous: string, self: string} = {
      next: "",
      first: "",
      last: "",
      previous: "",
      self: ""
  }

  private cursor: {current: number, previous: number, next: number, limit: number} = {
      current: 0,
      previous: 0,
      next: 0,
      limit: 0
  }

  public currentPage = 0
  public currentSort = ""

  constructor (provider?: INProvider) {
      this.provider = provider || new INProvider()
      return this
  }

  public async getData (): Promise<any> {
      return this.dataTraitment(await this.provider.getDataAndLink("", this.provider.genSortParam(this.currentSort)))
  }

  private dataTraitment (data: any): object {
      this.setLinks(data.links)
      this.setCursor()
      return data.data
  }

  private setLinks (providerLinks: {next: string, first: string, last: string, prev: string, self: string}): void {
      this.links.next = providerLinks.next
      this.links.first = providerLinks.first
      this.links.last = providerLinks.last
      this.links.previous = providerLinks.prev
      this.links.self = providerLinks.self
  }

  private setCursor (): void {
      this.currentPage = this.extractCursorFromLink(this.links.self)
  }

  private extractCursorFromLink (link: string): number {
      const limitPos = link.match(/[?&]limit=[0-9]*/g)
      const offsetPos = link.match(/[?&]offset=[0-9]*/g)
      if (!limitPos || !offsetPos) {
          return 0
      }
      const offset = offsetPos[0].substr(offsetPos[0].indexOf("=") + 1)
      const limit = limitPos[0].substr(limitPos[0].indexOf("=") + 1)
      if (offset === "0" || limit === "0") {
          return 1
      }

      return (Math.ceil(parseInt(offset) / parseInt(limit)) + 1)
  }

  public async next (): Promise<any> {
      return this.dataTraitment(await this.provider.getDataAndLink(this.getNextUrl()))
  }

  public async previous (): Promise<any> {
      return this.dataTraitment(await this.provider.getDataAndLink(this.getPreviousUrl()))
  }

  public async last (): Promise<any> {
      return this.dataTraitment(await this.provider.getDataAndLink(this.getLastUrl()))
  }

  public async first (): Promise<any> {
      return this.dataTraitment(await this.provider.getDataAndLink(this.getFirstUrl()))
  }

  public hasNext (): boolean {
      return typeof (this.links.next) !== "undefined" && this.links.next !== "" && this.links.next !== this.links.self
  }

  public hasPrevious (): boolean {
      return typeof (this.links.previous) !== "undefined" && this.links.previous !== "" && this.currentPage > 1
  }

  public hasFirst (): boolean {
      return typeof (this.links.first) !== "undefined" && this.links.first !== "" && this.currentPage > 1
  }

  public hasLast (): boolean {
      return typeof (this.links.last) !== "undefined" && this.links.last !== "" && this.links.last !== this.links.self
  }

  private getNextUrl (): string {
      return this.hasNext() ? this.links.next : ""
  }

  private getPreviousUrl (): string {
      return this.hasPrevious() ? this.links.previous : ""
  }

  private getFirstUrl (): string {
      return this.hasFirst() ? this.links.first : ""
  }

  private getLastUrl (): string {
      return this.hasLast() ? this.links.last : ""
  }
}
