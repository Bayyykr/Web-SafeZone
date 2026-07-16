import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/css/auth.css",
                "resources/css/admin.css",
                "resources/css/admin/pages/dashboard.css",
                "resources/css/admin/pages/cctv.css",
                "resources/css/admin/pages/infographic.css",
                "resources/css/admin/pages/report.css",
                "resources/css/admin/pages/user.css",
                "resources/css/admin/pages/master.css",
                "resources/js/app.js",
                "resources/js/admin.js",
                "resources/js/admin/users.js",
                "resources/js/admin/polsek.js",
                "resources/js/admin/kategori.js",
                "resources/js/charts.js",
                "resources/js/sos-map.js",
            ],
            refresh: true,
        }),
    ],
});
