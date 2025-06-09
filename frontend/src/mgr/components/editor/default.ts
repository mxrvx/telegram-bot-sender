import EditorJS from '@editorjs/editorjs'
import type {LogLevels, OutputData} from '@editorjs/editorjs'
import RuLexicon from './lexicons/ru'
import Undo from 'editorjs-undo'

import Header from '@editorjs/header'
import Paragraph from '@editorjs/paragraph'
import InlineCode from '@editorjs/inline-code'
import Link from './blocks/link'
import UnderLine from './blocks/underline'
import Strike from './blocks/strike'
import Emoji from './blocks/emoji'
import Image from './blocks/image'
import Video from './blocks/video'

const messages = $config.locale === 'ru' ? RuLexicon : undefined
const editor = ref(null)
export const record = ref(null)

async function onChange() {
    try {
        if (editor.value && typeof editor.value.save === 'function') {
            record.value = await editor.value.save()
        }
    } catch (e) {
        console.error(e)
    }
}

export function initEditor(id?: string, config?: object) {
    try {
        editor.value = new EditorJS({
            holder: id || 'editorjs',
            autofocus: !0,
            placeholder: 'Начните вводить текст...',
            onChange,
            data: record.value as OutputData,
            logLevel: 'ERROR' as LogLevels,
            i18n: {messages: messages},
            hideToolbar: true,
            inlineToolbar: true,
            tools: {
                paragraph: {
                    class: Paragraph,
                    inlineToolbar: true,
                    config: {
                        preserveBlank: true,
                    },
                },
                code: {
                    class: InlineCode,
                },
                underline: {
                    class: UnderLine,
                },
                strike: {
                    class: Strike,
                },
                link: {
                    class: Link,
                },
                emoji: {
                    class: Emoji,
                },
                image: {
                    class: Image,
                },
                video: {
                    class: Video,
                },
            },

            onReady: () => {
                new Undo({
                    editor: editor.value,
                    maxLength: 20,
                    config: {
                        debounceTimer: 100,
                        shortcuts: {
                            undo: 'CMD+Z',
                            redo: 'CMD+SHIFT+Z',
                        },
                    },
                })

                if (editor.value.configuration && editor.value.configuration.autofocus) {
                    setTimeout(() => {
                        editor.value.caret.focus(true)
                    }, 200)
                }
            },
            ...(config || {}),
        })
    } catch (e) {}
}

export function destroyEditor() {
    if (editor.value) {
        if (editor.value.destroy) {
            editor.value.destroy()
        }
    }
    record.value = {data: []}
}

export function resetEditor() {
    destroyEditor()
    initEditor()
}
