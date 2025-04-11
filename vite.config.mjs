/** @type {import('vite').UserConfig} */
import tailwindcss from "@tailwindcss/vite";

export default {
  build: {
    assetsDir: "",
    manifest: true,
    rollupOptions: {
      input: ["js/kanban.js"],
    },
  },
  plugins: [tailwindcss()],
};
