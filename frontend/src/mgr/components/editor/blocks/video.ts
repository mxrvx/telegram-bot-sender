import VideoTool from '@weekwood/editorjs-video'

export default class Video extends VideoTool {
    constructor({api, config, data, readOnly}) {
        config.player = {
            ...{
                pip: true,
                controls: true,
                light: false,
                playing: false,
            },
            ...(config.player || {}),
        }

        config.endpoints = {
            byFile: $config.api_url + 'mgr/editor/upload/video/',
        }
        config.captionPlaceholder = config.captionPlaceholder || 'Заголовок'

        super({api, config, data, readOnly})
    }

    static get toolbox() {
        return {
            title: 'Video',
            icon: `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
               <rect x="3" y="5" width="14" height="14" rx="2" ry="2" stroke="currentColor" stroke-width="2" fill="none"/>
               <polygon points="17,9 22,7 22,17 17,15" fill="currentColor"/>
             </svg>`,
        }
    }

    onUpload(e) {
        e.file ? (this.video = e.file) : this.uploadingFailed('incorrect response: ' + JSON.stringify(e))
    }

    uploadingFailed(e) {
        console.log('Video Tool: uploading failed because of', e)

        this.api.notifier.show({
            message: e.body ?? this.api.i18n.t('Couldn’t upload video. Please try another.'),
            style: 'error',
        })

        this.ui.hidePreloader()
    }

    setTune(e, t) {
        var n = this
        ;(this._data[e] = t),
            this.ui.applyTune(e, t),
            'stretched' === e &&
                Promise.resolve()
                    .then(function () {
                        const index = n.api.blocks.getCurrentBlockIndex()

                        if (index >= 0) {
                            const block = n.api.blocks.getBlockByIndex(index)
                            if (block) {
                                block.stretched = value
                            } else {
                                console.warn('Block not found at index', index)
                            }
                        }
                    })
                    .catch(function (e) {
                        console.error(e)
                    })
    }
}
