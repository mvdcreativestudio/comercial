// vite.config.mjs
import { defineConfig } from "file:///var/www/node_modules/vite/dist/node/index.js";
import laravel from "file:///var/www/node_modules/laravel-vite-plugin/dist/index.js";
import html from "file:///var/www/node_modules/@rollup/plugin-html/dist/es/index.js";
import { glob } from "file:///var/www/node_modules/glob/dist/esm/index.js";
function GetFilesArray(query) {
  return glob.sync(query);
}
var pageJsFiles = GetFilesArray("resources/assets/js/*.js");
var pageCssFiles = GetFilesArray("resources/assets/css/*.css");
var vendorJsFiles = GetFilesArray("resources/assets/vendor/js/*.js");
var LibsJsFiles = GetFilesArray("resources/assets/vendor/libs/**/*.js");
var CoreScssFiles = GetFilesArray("resources/assets/vendor/scss/**/!(_)*.scss");
var LibsScssFiles = GetFilesArray("resources/assets/vendor/libs/**/!(_)*.scss");
var LibsCssFiles = GetFilesArray("resources/assets/vendor/libs/**/*.css");
var FontsScssFiles = GetFilesArray("resources/assets/vendor/fonts/!(_)*.scss");
function libsWindowAssignment() {
  return {
    name: "libsWindowAssignment",
    transform(src, id) {
      if (id.includes("jkanban.js")) {
        return src.replace("this.jKanban", "window.jKanban");
      } else if (id.includes("vfs_fonts")) {
        return src.replaceAll("this.pdfMake", "window.pdfMake");
      }
    }
  };
}
var vite_config_default = defineConfig({
  server: {
    host: "0.0.0.0",
    // Permitir conexiones desde fuera del contenedor
    hmr: {
      host: "localhost"
    },
    watch: {
      usePolling: true
      // Útil para entornos de Docker
    }
  },
  plugins: [
    laravel({
      input: [
        "resources/css/app.css",
        "resources/js/app.js",
        ...pageJsFiles,
        ...vendorJsFiles,
        ...LibsJsFiles,
        "resources/js/laravel-user-management.js",
        // Processing Laravel User Management CRUD JS File
        ...CoreScssFiles,
        ...LibsScssFiles,
        ...LibsCssFiles,
        ...FontsScssFiles,
        ...pageCssFiles
      ],
      refresh: true
    }),
    html(),
    libsWindowAssignment()
  ]
});
export {
  vite_config_default as default
};
//# sourceMappingURL=data:application/json;base64,ewogICJ2ZXJzaW9uIjogMywKICAic291cmNlcyI6IFsidml0ZS5jb25maWcubWpzIl0sCiAgInNvdXJjZXNDb250ZW50IjogWyJjb25zdCBfX3ZpdGVfaW5qZWN0ZWRfb3JpZ2luYWxfZGlybmFtZSA9IFwiL3Zhci93d3dcIjtjb25zdCBfX3ZpdGVfaW5qZWN0ZWRfb3JpZ2luYWxfZmlsZW5hbWUgPSBcIi92YXIvd3d3L3ZpdGUuY29uZmlnLm1qc1wiO2NvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9pbXBvcnRfbWV0YV91cmwgPSBcImZpbGU6Ly8vdmFyL3d3dy92aXRlLmNvbmZpZy5tanNcIjtpbXBvcnQgeyBkZWZpbmVDb25maWcgfSBmcm9tICd2aXRlJztcbmltcG9ydCBsYXJhdmVsIGZyb20gJ2xhcmF2ZWwtdml0ZS1wbHVnaW4nO1xuaW1wb3J0IGh0bWwgZnJvbSAnQHJvbGx1cC9wbHVnaW4taHRtbCc7XG5pbXBvcnQgeyBnbG9iIH0gZnJvbSAnZ2xvYic7XG5cbi8qKlxuICogR2V0IEZpbGVzIGZyb20gYSBkaXJlY3RvcnlcbiAqIEBwYXJhbSB7c3RyaW5nfSBxdWVyeVxuICogQHJldHVybnMgYXJyYXlcbiAqL1xuZnVuY3Rpb24gR2V0RmlsZXNBcnJheShxdWVyeSkge1xuICByZXR1cm4gZ2xvYi5zeW5jKHF1ZXJ5KTtcbn1cbi8qKlxuICogSnMgRmlsZXNcbiAqL1xuLy8gUGFnZSBKUyBGaWxlc1xuY29uc3QgcGFnZUpzRmlsZXMgPSBHZXRGaWxlc0FycmF5KCdyZXNvdXJjZXMvYXNzZXRzL2pzLyouanMnKTtcblxuLy8gUGFnZSBDU1MgRmlsZXNcbmNvbnN0IHBhZ2VDc3NGaWxlcyA9IEdldEZpbGVzQXJyYXkoJ3Jlc291cmNlcy9hc3NldHMvY3NzLyouY3NzJyk7XG5cbi8vIFByb2Nlc3NpbmcgVmVuZG9yIEpTIEZpbGVzXG5jb25zdCB2ZW5kb3JKc0ZpbGVzID0gR2V0RmlsZXNBcnJheSgncmVzb3VyY2VzL2Fzc2V0cy92ZW5kb3IvanMvKi5qcycpO1xuXG4vLyBQcm9jZXNzaW5nIExpYnMgSlMgRmlsZXNcbmNvbnN0IExpYnNKc0ZpbGVzID0gR2V0RmlsZXNBcnJheSgncmVzb3VyY2VzL2Fzc2V0cy92ZW5kb3IvbGlicy8qKi8qLmpzJyk7XG5cbi8qKlxuICogU2NzcyBGaWxlc1xuICovXG4vLyBQcm9jZXNzaW5nIENvcmUsIFRoZW1lcyAmIFBhZ2VzIFNjc3MgRmlsZXNcbmNvbnN0IENvcmVTY3NzRmlsZXMgPSBHZXRGaWxlc0FycmF5KCdyZXNvdXJjZXMvYXNzZXRzL3ZlbmRvci9zY3NzLyoqLyEoXykqLnNjc3MnKTtcblxuLy8gUHJvY2Vzc2luZyBMaWJzIFNjc3MgJiBDc3MgRmlsZXNcbmNvbnN0IExpYnNTY3NzRmlsZXMgPSBHZXRGaWxlc0FycmF5KCdyZXNvdXJjZXMvYXNzZXRzL3ZlbmRvci9saWJzLyoqLyEoXykqLnNjc3MnKTtcbmNvbnN0IExpYnNDc3NGaWxlcyA9IEdldEZpbGVzQXJyYXkoJ3Jlc291cmNlcy9hc3NldHMvdmVuZG9yL2xpYnMvKiovKi5jc3MnKTtcblxuLy8gUHJvY2Vzc2luZyBGb250cyBTY3NzIEZpbGVzXG5jb25zdCBGb250c1Njc3NGaWxlcyA9IEdldEZpbGVzQXJyYXkoJ3Jlc291cmNlcy9hc3NldHMvdmVuZG9yL2ZvbnRzLyEoXykqLnNjc3MnKTtcblxuLy8gUHJvY2Vzc2luZyBXaW5kb3cgQXNzaWdubWVudCBmb3IgTGlicyBsaWtlIGpLYW5iYW4sIHBkZk1ha2VcbmZ1bmN0aW9uIGxpYnNXaW5kb3dBc3NpZ25tZW50KCkge1xuICByZXR1cm4ge1xuICAgIG5hbWU6ICdsaWJzV2luZG93QXNzaWdubWVudCcsXG5cbiAgICB0cmFuc2Zvcm0oc3JjLCBpZCkge1xuICAgICAgaWYgKGlkLmluY2x1ZGVzKCdqa2FuYmFuLmpzJykpIHtcbiAgICAgICAgcmV0dXJuIHNyYy5yZXBsYWNlKCd0aGlzLmpLYW5iYW4nLCAnd2luZG93LmpLYW5iYW4nKTtcbiAgICAgIH0gZWxzZSBpZiAoaWQuaW5jbHVkZXMoJ3Zmc19mb250cycpKSB7XG4gICAgICAgIHJldHVybiBzcmMucmVwbGFjZUFsbCgndGhpcy5wZGZNYWtlJywgJ3dpbmRvdy5wZGZNYWtlJyk7XG4gICAgICB9XG4gICAgfVxuICB9O1xufVxuXG5leHBvcnQgZGVmYXVsdCBkZWZpbmVDb25maWcoe1xuICBzZXJ2ZXI6IHtcbiAgICBob3N0OiAnMC4wLjAuMCcsIC8vIFBlcm1pdGlyIGNvbmV4aW9uZXMgZGVzZGUgZnVlcmEgZGVsIGNvbnRlbmVkb3JcbiAgICBobXI6IHtcbiAgICAgIGhvc3Q6ICdsb2NhbGhvc3QnLFxuICAgIH0sXG4gICAgd2F0Y2g6IHtcbiAgICAgIHVzZVBvbGxpbmc6IHRydWUsIC8vIFx1MDBEQXRpbCBwYXJhIGVudG9ybm9zIGRlIERvY2tlclxuICAgIH0sXG4gIH0sXG4gIHBsdWdpbnM6IFtcbiAgICBsYXJhdmVsKHtcbiAgICAgIGlucHV0OiBbXG4gICAgICAgICdyZXNvdXJjZXMvY3NzL2FwcC5jc3MnLFxuICAgICAgICAncmVzb3VyY2VzL2pzL2FwcC5qcycsXG4gICAgICAgIC4uLnBhZ2VKc0ZpbGVzLFxuICAgICAgICAuLi52ZW5kb3JKc0ZpbGVzLFxuICAgICAgICAuLi5MaWJzSnNGaWxlcyxcbiAgICAgICAgJ3Jlc291cmNlcy9qcy9sYXJhdmVsLXVzZXItbWFuYWdlbWVudC5qcycsIC8vIFByb2Nlc3NpbmcgTGFyYXZlbCBVc2VyIE1hbmFnZW1lbnQgQ1JVRCBKUyBGaWxlXG4gICAgICAgIC4uLkNvcmVTY3NzRmlsZXMsXG4gICAgICAgIC4uLkxpYnNTY3NzRmlsZXMsXG4gICAgICAgIC4uLkxpYnNDc3NGaWxlcyxcbiAgICAgICAgLi4uRm9udHNTY3NzRmlsZXMsXG4gICAgICAgIC4uLnBhZ2VDc3NGaWxlc1xuICAgICAgXSxcbiAgICAgIHJlZnJlc2g6IHRydWVcbiAgICB9KSxcbiAgICBodG1sKCksXG4gICAgbGlic1dpbmRvd0Fzc2lnbm1lbnQoKVxuICBdXG59KTtcbiJdLAogICJtYXBwaW5ncyI6ICI7QUFBNE0sU0FBUyxvQkFBb0I7QUFDek8sT0FBTyxhQUFhO0FBQ3BCLE9BQU8sVUFBVTtBQUNqQixTQUFTLFlBQVk7QUFPckIsU0FBUyxjQUFjLE9BQU87QUFDNUIsU0FBTyxLQUFLLEtBQUssS0FBSztBQUN4QjtBQUtBLElBQU0sY0FBYyxjQUFjLDBCQUEwQjtBQUc1RCxJQUFNLGVBQWUsY0FBYyw0QkFBNEI7QUFHL0QsSUFBTSxnQkFBZ0IsY0FBYyxpQ0FBaUM7QUFHckUsSUFBTSxjQUFjLGNBQWMsc0NBQXNDO0FBTXhFLElBQU0sZ0JBQWdCLGNBQWMsNENBQTRDO0FBR2hGLElBQU0sZ0JBQWdCLGNBQWMsNENBQTRDO0FBQ2hGLElBQU0sZUFBZSxjQUFjLHVDQUF1QztBQUcxRSxJQUFNLGlCQUFpQixjQUFjLDBDQUEwQztBQUcvRSxTQUFTLHVCQUF1QjtBQUM5QixTQUFPO0FBQUEsSUFDTCxNQUFNO0FBQUEsSUFFTixVQUFVLEtBQUssSUFBSTtBQUNqQixVQUFJLEdBQUcsU0FBUyxZQUFZLEdBQUc7QUFDN0IsZUFBTyxJQUFJLFFBQVEsZ0JBQWdCLGdCQUFnQjtBQUFBLE1BQ3JELFdBQVcsR0FBRyxTQUFTLFdBQVcsR0FBRztBQUNuQyxlQUFPLElBQUksV0FBVyxnQkFBZ0IsZ0JBQWdCO0FBQUEsTUFDeEQ7QUFBQSxJQUNGO0FBQUEsRUFDRjtBQUNGO0FBRUEsSUFBTyxzQkFBUSxhQUFhO0FBQUEsRUFDMUIsUUFBUTtBQUFBLElBQ04sTUFBTTtBQUFBO0FBQUEsSUFDTixLQUFLO0FBQUEsTUFDSCxNQUFNO0FBQUEsSUFDUjtBQUFBLElBQ0EsT0FBTztBQUFBLE1BQ0wsWUFBWTtBQUFBO0FBQUEsSUFDZDtBQUFBLEVBQ0Y7QUFBQSxFQUNBLFNBQVM7QUFBQSxJQUNQLFFBQVE7QUFBQSxNQUNOLE9BQU87QUFBQSxRQUNMO0FBQUEsUUFDQTtBQUFBLFFBQ0EsR0FBRztBQUFBLFFBQ0gsR0FBRztBQUFBLFFBQ0gsR0FBRztBQUFBLFFBQ0g7QUFBQTtBQUFBLFFBQ0EsR0FBRztBQUFBLFFBQ0gsR0FBRztBQUFBLFFBQ0gsR0FBRztBQUFBLFFBQ0gsR0FBRztBQUFBLFFBQ0gsR0FBRztBQUFBLE1BQ0w7QUFBQSxNQUNBLFNBQVM7QUFBQSxJQUNYLENBQUM7QUFBQSxJQUNELEtBQUs7QUFBQSxJQUNMLHFCQUFxQjtBQUFBLEVBQ3ZCO0FBQ0YsQ0FBQzsiLAogICJuYW1lcyI6IFtdCn0K
