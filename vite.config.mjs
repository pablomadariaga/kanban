/** @type {import('vite').UserConfig} */

export default {
  build: {
    assetsDir: "",
    manifest: true,
    rollupOptions: {
      input: ["js/kanban.js"],
    },
  },
  plugins: [],
};
