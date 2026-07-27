import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            colors: {
                primary: {
                    DEFAULT: "#0F766E",
                    50: "#F0FDFA",
                    600: "#0F766E",
                    700: "#115E59",
                }, // teal, not the generic hospital blue
                accent: "#EA580C", // warm clay-orange for alerts/CTAs, used sparingly
                surface: "#F8FAFC",
            },
            fontFamily: {
                display: ['"Fraunces"', "serif"], // headings — gives it a human, less "template" feel
                sans: ['"Inter"', "sans-serif"], // body/UI
            },
        },
    },

    plugins: [forms],
};
