import EmojiPicker from '@plebjs/editorjs-emoji-picker-tool'

export default class EmojiP extends EmojiPicker {
    constructor({api, config}) {
        super({api, config})
    }

    renderActions() {
        const result = super.renderActions()

        if (this.pickerZone) {
            const style = document.createElement('style')
            style.textContent = '.search-row, .pad-top { display: none; }'
            this.pickerZone.querySelector('emoji-picker').shadowRoot.appendChild(style)
        }

        return result
    }
}
