module.exports = {
    preset: "@vue/cli-plugin-unit-jest/presets/typescript",
    testMatch: ["<rootDir>/javascript/src/tests/**/*.test.ts", "<rootDir>/modules/*/vue/tests/*/*.test.ts"],
    reporters: ["default", "jest-junit"],
    testResultsProcessor: "jest-junit",
    setupFiles: ["./javascript/config/jest-prestart.js"],
    moduleNameMapper: {
        "^@\/(.*)$": "<rootDir>/javascript/src/$1",
        "^@modules\/(.*)$": "<rootDir>/modules/$1",
        "vuetify/lib(.*)": "<rootDir>/node_modules/vuetify/es5$1"
    },
    transformIgnorePatterns: [
        "<rootDir>/node_modules/(?!(vuetify)/)"
    ],
    cacheDirectory: "<rootDir>/tmp/jest_cache",
    coverageReporters: ["html", "json-summary", "text"]
}
