import {IconStrikethrough} from '@codexteam/icons'
import {API, InlineTool, InlineToolConstructorOptions, SanitizerConfig} from '@editorjs/editorjs'

interface IconClasses {
    base: string
    active: string
}

export default class Strike implements InlineTool {
    private api: API

    private button: HTMLButtonElement | null

    private tag: string = 'S'

    private iconClasses: IconClasses

    static get CSS(): string {
        return 'strike'
    }

    constructor({api}: InlineToolConstructorOptions) {
        this.api = api

        this.button = null

        this.iconClasses = {
            base: this.api.styles.inlineToolButton,
            active: this.api.styles.inlineToolButtonActive,
        }
    }

    static get isInline(): boolean {
        return true
    }

    render(): HTMLElement {
        this.button = document.createElement('button')
        this.button.type = 'button'
        this.button.classList.add(this.iconClasses.base)
        this.button.innerHTML = this.toolboxIcon

        return this.button
    }

    surround(range: Range): void {
        if (!range) {
            return
        }

        let termWrapper = this.api.selection.findParentTag(this.tag, Strike.CSS) as HTMLElement

        if (termWrapper) {
            this.unwrap(termWrapper)
        } else {
            this.wrap(range)
        }
    }

    wrap(range: Range): void {
        let span = document.createElement(this.tag)

        span.classList.add(Strike.CSS)

        span.appendChild(range.extractContents())
        range.insertNode(span)

        this.api.selection.expandToTag(span)
    }

    unwrap(termWrapper: HTMLElement): void {
        this.api.selection.expandToTag(termWrapper)

        const sel = window.getSelection()
        if (!sel) return

        const range = sel.getRangeAt(0)
        const unwrappedContent = range.extractContents()

        termWrapper.parentNode?.removeChild(termWrapper)

        range.insertNode(unwrappedContent)

        sel.removeAllRanges()
        sel.addRange(range)
    }

    checkState(): boolean {
        const termTag = this.api.selection.findParentTag(this.tag, Strike.CSS)

        if (this.button) {
            this.button.classList.toggle(this.iconClasses.active, !!termTag)
        }

        return !!termTag
    }

    get toolboxIcon(): string {
        return IconStrikethrough
    }

    static get sanitize(): SanitizerConfig {
        return {
            s: {
                class: Strike.CSS,
            },
        }
    }
}
