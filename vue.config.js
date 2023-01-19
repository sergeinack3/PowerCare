const path = require("path")
const glob = require("glob")
const fs = require("fs")
const now = require("performance-now")
const webpack = require("webpack")

// Custom command line params
console.log("\x1b[36m%s\x1b[0m", "Parameters processing...")
const startParam = now()
const _params = process.argv.slice(3)
const params = {}
_params.forEach((param) => {
    if (param.includes("=")) {
        const paramName = param.match(/^--.*=/gmi)[0].slice(2).slice(0, -1)
        const paramValue = param.match(/=.*$/gmi)[0].slice(1)
        params[paramName] = paramValue.split(" ")
    }
})
const endParam = now()
console.log("\x1b[32m%s\x1b[0m", `Parameters checked in ${parseFloat(endParam - startParam).toFixed(2)} milliseconds`)

// Modules list generation process => generated in javascript/src/core/utils/modulesList.json file
console.log("\x1b[36m%s\x1b[0m", "Modules list generation processing...")
const startModulesListGeneration = now()
const modulesListFile = "javascript/src/core/utils/modulesList.json"
const modulesList = []

try {
    fs.writeFileSync(modulesListFile, "{\n    \"modules\": [\n")

    fs.readdirSync("modules/").forEach((file, idx, array) => {
        modulesList.push(file)
        fs.appendFileSync(modulesListFile, "\"" + file)

        idx === array.length - 1
            ? fs.appendFileSync(modulesListFile, "\"\n")
            : fs.appendFileSync(modulesListFile, "\",\n")
    })

    fs.appendFileSync(modulesListFile, "    ]\n}\n")
    console.log("\x1b[32m%s\x1b[0m", "File modulesList.json created successfully")
}
catch (err) {
    console.error(err)
}

const endModulesListGeneration = now()
console.log("\x1b[32m%s\x1b[0m", `Modules list generated in ${parseFloat(endModulesListGeneration - startModulesListGeneration).toFixed(2)} milliseconds`)

// All entries that webpack should compile
console.log("\x1b[36m%s\x1b[0m", "Entries definition...")
const startEntries = now()
let pages = {
    appbar: {
        entry: "javascript/src/main.js"
    }
}

// Entry files definition locations
let globPattern = "modules/*/vue/*entry.json"
// Custom entry files definition
if ("modules" in params) {
    globPattern = "modules/@(" + params.modules.join("|") + ")/vue/*entry.json"
}

const extraPages = glob.sync(globPattern)
extraPages.forEach((file) => {
    pages = { ...pages, ...JSON.parse(fs.readFileSync(file).toString()) }
})
const stopEntries = now()
console.log("\x1b[32m%s\x1b[0m", `Entries defined in ${parseFloat(stopEntries - startEntries).toFixed(2)} milliseconds`)

module.exports = {
    outputDir: "./javascript/dist",
    publicPath: "./javascript/dist/",
    filenameHashing: false,
    pages: pages,
    runtimeCompiler: true,
    productionSourceMap: process.env.NODE_ENV !== "production",
    css: {
        extract: false,
        loaderOptions: {
            sass: {
                additionalData: "@import \"~oxify/src/styles/variables\""
            },
            scss: {
                additionalData: "@import \"~oxify/src/styles/variables\";"
            }
        }
    },
    configureWebpack: {
        output: {
            chunkFilename: "[name].[contenthash].js"
        },
        plugins: [
            new webpack.IgnorePlugin({
                checkResource (resource, context) {
                    const resourceModuleName = resource.match(/^@modules\/([a-zA-Z0-9_]+)/)

                    return resourceModuleName ? !modulesList.includes(resourceModuleName[1]) : false
                }
            })
        ],
        resolve: {
            alias: {
                "@": path.resolve(__dirname, "./javascript/src/"),
                assets: path.resolve(__dirname, "./images/assets_vue"),
                "@modules": path.resolve(__dirname, "modules")
            }
        },
        cache: {
            type: "filesystem"
        },
        optimization: {
            runtimeChunk: "single",
            realContentHash: true,
            splitChunks: {
                chunks: "all",
                minSize: 20000,
                minRemainingSize: 0,
                minChunks: 1,
                maxAsyncRequests: 30,
                maxInitialRequests: 30,
                enforceSizeThreshold: 50000,
                cacheGroups: {
                    defaultVendors: {
                        test: /[\\/]node_modules[\\/]/,
                        priority: -10,
                        reuseExistingChunk: true
                    }
                }
            }
        }
    },

    chainWebpack: config => {
        config.plugins.delete("html")
        config.plugins.delete("preload")
        config.plugins.delete("copy")
        Object.keys(pages).forEach(page => {
            config.plugins.delete(`html-${page}`)
        })
        config.module.rule("svg").set("generator", {
            filename: "img/[hash][name][ext]"
        })
        config.module.rule("svg").set("dependency", { not: ["url"] })
        config.module.rule("svg").type("asset/source")
    }
}
