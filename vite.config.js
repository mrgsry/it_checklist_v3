import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/sass/app.scss", "resources/js/app.js"],
            refresh: true,
        }),
    ],
    server: {
        host: true,
        port: 5175,
        hmr: {
            host: "192.168.21.113",
            protocol: "ws",
        },
    },
});
