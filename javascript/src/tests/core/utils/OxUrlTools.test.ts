/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import { tr } from "@/core/utils/OxTranslator"
import { getUrlParams, OxUrlBuilder } from "@/core/utils/OxUrlTools"

/**
 * OxUrlTools tests
 */
export default class OxUrlToolsTest extends OxTest {
    protected component = "OxUrlTools"

    public testGetUrlParams () {
        const url = "http://test/route?fieldsets=fieldset1,fieldset2.value&relations=relation1,relation2&filter=name.contains.test,code.equal.te&sort=duration,-name&offset=50&limit=20&search=test&testParameter=test&otherParameter=ok"
        const result = getUrlParams(url)
        expect(result).toEqual({
            fieldsets: ["fieldset1", "fieldset2.value"],
            relations: ["relation1", "relation2"],
            filters: ["name.contains.test", "code.equal.te"],
            sort: [
                { sort: "ASC", choice: "duration" },
                { sort: "DESC", choice: "name" }
            ],
            offset: 50,
            limit: 20,
            search: "test",
            otherParameters: []
        })
    }

    public testOxUrlBuilderDefaultInitialization () {
        const baseUrl = "http://test/route?fieldsets=fieldset1,fieldset2.value&relations=relation1,relation2&filter=name.contains.test,code.equal.te&sort=duration,-name&offset=50&limit=20&search=test&testParameter=test&otherParameter=ok"
        const url = new OxUrlBuilder(baseUrl)
        expect(url.queryParameters).toEqual({
            fieldsets: ["fieldset1", "fieldset2.value"],
            relations: ["relation1", "relation2"],
            filters: ["name.contains.test", "code.equal.te"],
            sort: ["duration", "-name"],
            offset: "50",
            limit: "20",
            search: "test"
        })
        expect(url.otherParameters).toEqual([])
        expect(url.toString().startsWith("http://test")).toBeTruthy()
    }

    public testOxUrlBuilderWithIncompleteUrl () {
        const baseUrl = "/route?fieldsets=fieldset1,fieldset2.value&relations=relation1,relation2&filter=name.contains.test,code.equal.te&sort=duration,-name&offset=50&limit=20&search=test&testParameter=test&otherParameter=ok"
        const url = new OxUrlBuilder(baseUrl)
        expect(url.toString().startsWith(window.location.origin + "/route")).toBeTruthy()
    }

    public testAddExistingFieldset () {
        const url = new OxUrlBuilder()
        url.addFieldset("test")
        url.addFieldset("test")
        expect(url.queryParameters.fieldsets).toEqual(["test"])
    }

    public testAddExistingRelation () {
        const url = new OxUrlBuilder()
        url.addRelation("testRelation")
        url.addRelation("testRelation")
        expect(url.queryParameters.relations).toEqual(["testRelation"])
    }

    public testWithFilters () {
        const url = new OxUrlBuilder().withFilters(
            {
                key: "test1",
                operator: "contains",
                value: "value1"
            },
            {
                key: "test2",
                operator: "in",
                value: ["value2", "value22"]
            }
        )
        expect(url.queryParameters.filters).toEqual([
            "test1.contains.value1",
            "test2.in.value2.value22"
        ])
    }

    public testAddNewFilter () {
        const url = new OxUrlBuilder()
            .addFilter("test", "contains", "value")
            .addFilter("test", "greaterOrEqual", "10")
        expect(url.queryParameters.filters).toEqual([
            "test.contains.value",
            "test.greaterOrEqual.10"
        ])
    }

    public testAddExistingFilter () {
        const url = new OxUrlBuilder()
            .addFilter("test", "contains", "value")
            .addFilter("test", "contains", "value2")
        expect(url.queryParameters.filters).toEqual([
            "test.contains.value2"
        ])
    }

    public testWithSort () {
        const url = new OxUrlBuilder().withSort({ choice: "test", sort: "ASC" }, { choice: "test2", sort: "DESC" })
        expect(url.queryParameters.sort).toEqual([
            "test", "-test2"
        ])
    }

    public testAddNewSort () {
        const url = new OxUrlBuilder()
            .addSort({ choice: "test", sort: "ASC" })
            .addSort({ choice: "test2", sort: "DESC" })
        expect(url.queryParameters.sort).toEqual([
            "test", "-test2"
        ])
    }

    public testAddExistingSort () {
        const url = new OxUrlBuilder()
            .addSort({ choice: "test", sort: "ASC" })
            .addSort({ choice: "test", sort: "DESC" })
        expect(url.queryParameters.sort).toEqual([
            "-test"
        ])
    }

    public testRemoveParameter () {
        const url = new OxUrlBuilder().addParameter("param", "value")
        url.removeParameter("param")
        expect(url.toString()).not.toContain("param=value")
        expect(url.otherParameters).toEqual([])
    }

    public testBuildFullUrl () {
        const url = new OxUrlBuilder()
            .withSearch("testSearch")
            .withFieldsets(["default", "extra"])
            .withRelations(["relation1", "relation2"])
            .addParameter("custom", "value")
            .withPermissions()
            .withSchema()
        const result = url.toString()
        expect(result).toContain("search=test")
        expect(result).toContain("fieldsets=default%2Cextra")
        expect(result).toContain("relations=relation1%2Crelation2")
        expect(result).toContain("custom=value")
        expect(result).toContain("permissions=true")
        expect(result).toContain("schema=true")
    }
}

(new OxUrlToolsTest()).launchTests()
