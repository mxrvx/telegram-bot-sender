import LinkAutocomplete from '@editorjs/link-autocomplete'

export default class Link extends LinkAutocomplete {
    constructor({api, config}) {
        config = {
            endpointPath: 'mgr/editor/autocomplete/resource/',
            queryParam: 'query',
            ...config,
        }

        super({api, config})

        this.searchEndpointPath = this.config.endpointPath
    }

    get shortcut() {
        return ''
    }

    static get title() {
        return 'Link'
    }
    /**
     * Send search request
     *
     * @param {string} searchString - search string input
     *
     * @returns {Promise<SearchItemData[]>}
     */
    async searchRequest(searchString) {
        /**
         * Compose query string
         *
         * @type {string}
         */
        const queryString = new URLSearchParams({[this.searchQueryParam]: searchString}).toString()

        try {
            const searchResponseRaw = await fetch(`${$config.api_url}${this.searchEndpointPath}?${queryString}`)

            if (searchResponseRaw.ok) {
                const searchResponse = await searchResponseRaw.json()
                return searchResponse.results || []
            } else {
                console.warn('Error response:', response.status, response.statusText)
            }
        } catch (e) {
            notifier.show({
                message: `${DICTIONARY.searchRequestError} "${e.message}"`,
                style: 'error',
            })
        }

        return []
    }
}
