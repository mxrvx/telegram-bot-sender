// @ts-ignore
import path from 'path'
import ai from 'unplugin-auto-import/vite'
import withNamespace, {aiConfig} from './src/mgr/config'
import {terser} from 'rollup-plugin-terser'

export default withNamespace('mxrvx-telegram-bot-sender', {
    esbuild: {
        target: 'node18',
    },
    plugins: [],
    build: {
        manifest: 'manifest.json',
        emptyOutDir: true,
        //sourcemap: true,
        outDir: path.resolve(__dirname, '../assets/src/mgr'),
        assetsDir: '',
        rollupOptions: {
            plugins: [terser()],
            input: './src/mgr.js',
        },
        minify: 'terser',
        terserOptions: {
            format: {
                comments: false,
            },
        },
    },
    css: {
        preprocessorOptions: {
            scss: {
                quietDeps: true,
            },
        },
    },
})
