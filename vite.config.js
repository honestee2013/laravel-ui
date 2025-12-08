import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import fs from 'fs'
import path from 'path'

import { fileURLToPath } from 'url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))

// Read Laravel app base path from environment
const appRoot = process.env.APP_BASE_PATH || path.resolve(__dirname, '../../../../')




export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],

    build: {
        outDir: path.resolve(appRoot, 'public/vendor/qf'),
        emptyOutDir: true,
        manifest: true,
        assetsDir: 'assets',
        rollupOptions: {
            input: path.resolve(__dirname, 'resources/js/app.js'),
            output: {
                entryFileNames: 'assets/[name]-[hash].js',
                chunkFileNames: 'assets/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash][extname]',
            },
        },
        manifestFileName: 'manifest.json', // 👈 force manifest root
    },

    server: {
        port: 5174, // 👈 THIS controls the actual dev server port
        origin: 'http://127.0.0.1:5174',
        fs: {
            allow: ['..'],
        },
        hmr: {
            host: '127.0.0.1',
            port: 5174,
        },
    },

    // Custom hook: write vite.hot file into the vendor path
    configureServer(server) {
        const hotFile = path.resolve(__dirname, 'vite.hot')
        server.httpServer?.once('listening', () => {
            fs.writeFileSync(hotFile, 'http://127.0.0.1:5174')
        })
        server.httpServer?.once('close', () => {
            if (fs.existsSync(hotFile)) {
                fs.unlinkSync(hotFile)
            }
        })
    },
})
