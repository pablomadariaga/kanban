import colors from "tailwindcss/colors";

/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./**/*.php", "./src/**/*.js", "./**/**/*.blade.php"],
  darkMode: "class",
  theme: {},
  plugins: [require("./js/plugins/kaban")],
};
