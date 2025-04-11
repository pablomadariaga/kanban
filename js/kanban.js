import sort from "@alpinejs/sort";

document.addEventListener("alpine:init", () => {
  // Check if Alpine is loaded and if the sort plugin was already installed.
  if (Alpine && !Alpine._sortPluginInstalled) {
    Alpine.plugin(sort);
    Alpine._sortPluginInstalled = true; // Mark that the plugin has been installed.
  }
});
