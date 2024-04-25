import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
    "./storage/framework/views/*.php",
    "./resources/views/**/*.blade.php",
    "./resources/js/**/*.vue",
  ],

  theme: {
    extend: {
      fontFamily: {
        sans: ["Figtree", ...defaultTheme.fontFamily.sans],
      },
      colors: {
        primary: "#2986CC",
        "primary-shade": "#1c5d8f",
        "primary-tint": "#69aadb",
        secondary: "#16a34a",
        "secondary-shade": "#0f7234",
        "secondary-tint": "#4dbff2",
        tertiary: "#006fb4",
        "tertiary-shade": "#004e7e",
        "tertiary-tint": "#4d9acb",
        medium: "#3c4a3e",
        "medium-shade": "#778078",
        "medium-tint": "#6f7a71",
        light: "#9fafa1",
        "light-shade": "#6f7a71",
        "light-tint": "#bcc7bd",
      },
    },
  },

  plugins: [forms],
};
