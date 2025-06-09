interface AppConfig {
    [key: string]: any
}

class App extends Ext.Component {
    static singleton: boolean = true
    page: object = {}
    window: object = {}
    grid: object = {}
    tree: object = {}
    panel: object = {}
    combo: object = {}
    field: object = {}
    config: object = {}
    view: object = {}
    tools: object = {}

    constructor(config?: AppConfig) {
        config = config || {}
        super(config)
    }
}

Ext.reg('mxrvxtelegrambotsender', App)

export default App
