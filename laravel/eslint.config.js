// eslint.config.js

import globals from "globals";
import pluginJs from "@eslint/js";
import pluginReact from "eslint-plugin-react";
import pluginImport from "eslint-plugin-import";

export default [
    {
        files: ["**/*.{js,mjs,cjs,jsx}"],
        plugins: {
            react: pluginReact,
            import: pluginImport,
        },
        languageOptions: {
            globals: {
                ...globals.browser,
                route: "readonly",
            },
            parserOptions: {
                ecmaFeatures: {
                    jsx: true,
                },
            },
        },
        rules: {
            ...pluginJs.configs.recommended.rules,
            ...pluginReact.configs.recommended.rules,
            "react/react-in-jsx-scope": "off",
            "react/prop-types": "off",
            "import/no-unresolved": [2, { caseSensitive: true }],

            "no-unused-vars": "warn",
            "react/no-children-prop": "warn",
            "react/no-unescaped-entities": "warn",
        },
        settings: {
            react: {
                version: "detect",
            },
            "import/resolver": {
                alias: {
                    map: [["@", "./resources/js"]],
                    extensions: [".js", ".jsx"],
                },
                node: {
                    extensions: [".js", ".jsx"],
                },
            },
        },
    },
];
