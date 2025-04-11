import colors from "tailwindcss/colors";

/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./**/*.js", "./**/*.blade.php", "./**/**/*.blade.php"],
  darkMode: "class",
  theme: {},
  plugins: [require("./js/plugins/sortableStyles")],
};
