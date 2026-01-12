/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./packages/Webkul/Admin/src/Resources/**/*.blade.php",
        "./packages/Webkul/Admin/src/Resources/**/*.js",
        "./packages/Webkul/Admin/src/Resources/**/*.vue",
        "./packages/Webkul/Marketplace/src/Resources/**/*.blade.php",
        "./packages/Webkul/Marketplace/src/Resources/**/*.js",
        "./packages/Webkul/Marketplace/src/Resources/**/*.vue",
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],

    theme: {
        container: {
            center: true,

            screens: {
                "4xl": "1920px",
            },

            padding: {
                DEFAULT: "16px",
            },
        },

        screens: {
            sm: "525px",
            md: "768px",
            lg: "1024px",
            xl: "1240px",
            "2xl": "1440px",
            "3xl": "1680px",
            "4xl": "1920px",
        },

        extend: {
            colors: {
                brandColor: "var(--brand-color)",
            },

            fontFamily: {
                inter: ['Inter'],
                icon: ['icomoon']
            }
        },
    },

    darkMode: 'class',

    plugins: [],

    safelist: [
        {
            pattern: /icon-/,
        }
    ]
};
