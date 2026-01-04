import { defineConfig } from "vite"
import laravel from "laravel-vite-plugin"

export default defineConfig({
  plugins: [
    laravel({
      input: [
        "resources/scss/app.scss",
        "resources/css/app.css",
        "resources/css/notifications.css",
        "resources/js/app.js"
      ],
      refresh: true,
    }),
  ],
  build: {
    manifest: true,
  }
})