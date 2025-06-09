// @ts-ignore
import Mgr from './mgr/app.ts'
// import 'virtual:uno.css'
import './mgr/scss/index.scss'

declare global {
    interface Window {
        mxrvxTelegramBotSender: Mgr
    }
}

window.mxrvxTelegramBotSender = new Mgr($config)
import.meta.glob('./mgr/components/inject/*.{js,ts}', {eager: true})
