// @ts-ignore
import path from 'path'
import ai from 'unplugin-auto-import/vite'

import withNamespace, {aiConfig} from './src/web/config'

export default withNamespace('mxrvx-telegram-bot-sender', {
    plugins: [],
    build: {
        manifest: 'manifest.json',
        emptyOutDir: true,
        //sourcemap: true,
        outDir: path.resolve(__dirname, '../assets/src/web'),
        assetsDir: '',
        rollupOptions: {
            input: './src/web.js',
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
