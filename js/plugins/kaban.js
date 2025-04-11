import plugin from "tailwindcss/plugin";

module.exports = plugin(function ({ addUtilities }) {
  const utility = {
    ".sortable-ghost": {
      opacity: "50%",
    },
    ".sortable-chosen": {
      opacity: "50%",
    },
  };

  addUtilities(utility, ["dark", "responsive"]);
});
