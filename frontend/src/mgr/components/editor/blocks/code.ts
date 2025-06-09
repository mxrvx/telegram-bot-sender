import {IconBrackets} from '@codexteam/icons'
import {API, InlineTool, InlineToolConstructorOptions, SanitizerConfig} from '@editorjs/editorjs'

interface IconClasses {
    base: string
    active: string
}

export default class Code implements InlineTool {
    private api: API

    private button: HTMLButtonElement | null

    private tag: string = 'CODE'

    private iconClasses: IconClasses

    static get CSS(): string {
        return 'code'
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

        let termWrapper = this.api.selection.findParentTag(this.tag, Code.CSS) as HTMLElement

        if (termWrapper) {
            this.unwrap(termWrapper)
        } else {
            this.wrap(range)
        }
    }

    wrap(range: Range): void {
        let span = document.createElement(this.tag)

        span.classList.add(Code.CSS)

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
        const termTag = this.api.selection.findParentTag(this.tag, Code.CSS)

        if (this.button) {
            this.button.classList.toggle(this.iconClasses.active, !!termTag)
        }

        return !!termTag
    }

    get toolboxIcon(): string {
        return IconBrackets
    }

    static get sanitize(): SanitizerConfig {
        return {
            code: {
                class: Code.CSS,
            },
        }
    }
}
