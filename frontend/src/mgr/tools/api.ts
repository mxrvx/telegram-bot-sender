interface ApiRequestConfig {
    endpoint: string
    method?: string
    params?: object
    headers?: object
    listeners?: object
    [key: string]: any
}

export function apiRequest(config: ApiRequestConfig): Promise<any> | any {
    const {endpoint, method = 'GET', params = {}, headers = {}, listeners = {}, handler, ...requestOptions} = config

    if (handler === 'fetch') {
        return new Promise(async (resolve, reject) => {
            try {
                const body =
                    params instanceof FormData
                        ? params
                        : (() => {
                              const fd = new FormData()
                              for (const key in params) {
                                  fd.append(key, params[key])
                              }
                              return fd
                          })()

                const response = await fetch($config.api_url + endpoint, {
                    method: method.toUpperCase(),
                    headers: {
                        ...headers,
                    },
                    body: body,
                    ...requestOptions,
                })

                const text = await response.text()

                let data
                try {
                    data = JSON.parse(text)
                } catch {
                    data = text
                }

                if (response.ok) {
                    resolve(data)
                } else {
                    reject(data)
                }
            } catch (error) {
                reject(error)
            }
        })
    }

    return new Promise((resolve, reject) => {
        MODx.Ajax.request(
            Ext.apply(
                {
                    url: $config.api_url + endpoint,
                    method: method.toUpperCase(),
                    params: params,
                    headers: headers,
                    listeners: listeners,
                    callback: function (opts, success, response) {
                        try {
                            const data = Ext.decode(response.responseText)
                            if (success) {
                                resolve(data)
                            } else {
                                reject(data)
                            }
                        } catch (e) {
                            reject(e)
                        }
                    },
                },
                requestOptions,
            ),
        )
    })
}

export function getRequest(endpoint: string, config: ApiRequestConfig = {}) {
    return apiRequest({
        endpoint,
        method: 'GET',
        ...config,
    })
}

export function postRequest(endpoint: string, config: ApiRequestConfig = {}) {
    return apiRequest({
        endpoint,
        method: 'POST',
        ...config,
    })
}

export function putRequest(endpoint: string, config: ApiRequestConfig = {}) {
    return apiRequest({
        endpoint,
        method: 'PUT',
        ...config,
    })
}

export function patchRequest(endpoint: string, config: ApiRequestConfig = {}) {
    return apiRequest({
        endpoint,
        method: 'PATCH',
        ...config,
    })
}

export function deleteRequest(endpoint: string, config: ApiRequestConfig = {}) {
    return apiRequest({
        endpoint,
        method: 'DELETE',
        ...config,
    })
}
