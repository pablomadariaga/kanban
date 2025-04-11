import colors from "tailwindcss/colors";

/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./src/**/*.php", "./src/**/*.js", "./src/**/*.blade.php"],
  darkMode: "class",
  theme: {},
  plugins: [require("./js/plugins/kaban")],
};
