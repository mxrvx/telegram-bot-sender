import ImageTool from '@editorjs/image'

export default class Image extends ImageTool {
    constructor({data, config, api, readOnly, block}: ImageToolConstructorOptions) {
        config = {
            endpoints: {
                byFile: $config.api_url + 'mgr/editor/upload/image/',
            },
            uploader: {
                uploadByFile: async (file) => this.uploadByFile(file),
            },
            captionPlaceholder: 'Заголовок',
            ...config,
        }

        super({data, config, api, readOnly, block})
    }

    static get title() {
        return 'Image'
    }

    async uploadByFile(file) {
        const data = await $post('mgr/editor/upload/image/', {
            handler: 'fetch',
            params: {
                image: file,
            },
        })

        if (data.file) {
            return {
                success: 1,
                ...data,
            }
        }

        return data
    }

    uploadingFailed(e) {
        console.log('Image Tool: uploading failed because of', e)

        this.api.notifier.show({
            message: typeof e === 'string' ? e : this.api.i18n.t('Couldn’t upload image. Please try another.'),
            style: 'error',
        })

        this.ui.hidePreloader()
    }
}
