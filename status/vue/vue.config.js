const path = require("path")

const pages = {
    status: {
        entry: "status/vue/components/app.js"
    }
}

// Custom command line params
console.log("\x1b[36m%s\x1b[0m", "Build /status")
module.exports = {
    outputDir: "./status/vue/dist",
    filenameHashing: false,
    pages: pages,
    runtimeCompiler: true,
    productionSourceMap: false,
    css: { extract: false },
    chainWebpack: config => {
        config.optimization.delete("splitChunks")
        config.plugins.delete("html")
        config.plugins.delete("preload")
        config.plugins.delete("copy")
        config.plugin("fork-ts-checker").tap((args) => {
            args[0].typescript.configFile = "./status/vue/tsconfig.json"
            return args
        })
        Object.keys(pages).forEach(page => {
            config.plugins.delete(`html-${page}`)
        })
    }
}
