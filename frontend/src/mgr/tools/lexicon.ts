let lexiconKey: string

function getRecords() {
    return window[$ns]?.lexicon || {}
}

export function useLexicon(key: string, placeholders?: Record<string, string | number>, amount?: number | string) {
    const lexicon = getRecords()
    if (key in lexicon) {
        let value = lexicon[key]
        if (placeholders) {
            Object.keys(placeholders).forEach((i: string) => {
                value = value.replaceAll('{' + i + '}', placeholders[i])
            })
            if (amount !== undefined) {
                const tmp = value.split('|')
                const idx = pluralRule(Number(amount), tmp.length)
                if (tmp[idx]) {
                    value = tmp[idx]
                }
            }
        }
        return value
    }
    console.warn(`Could not load lexicon key: \`${key}\``)

    return key
}

export function getLexicon() {
    const expand = (obj) =>
        Object.keys(obj).reduce((res, k) => {
            k.split('.').reduce(
                (acc, e, i, keys) =>
                    acc[e] || (acc[e] = isNaN(Number(keys[i + 1])) ? (keys.length - 1 === i ? obj[k] : {}) : []),
                res,
            )
            return res
        }, {})

    return expand(getRecords())
}

function pluralRule(choice: number, choicesLength: number) {
    if (choice === 0) {
        return 0
    }

    const teen = choice > 10 && choice < 20
    const endsWithOne = choice % 10 === 1
    if (!teen && endsWithOne) {
        return 1
    }
    if (!teen && choice % 10 >= 2 && choice % 10 <= 4) {
        return 2
    }

    return choicesLength < 4 ? 2 : 3
}
