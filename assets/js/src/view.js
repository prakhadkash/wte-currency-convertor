const wpteccCookie = {
    get: cookieName => {
        const cookies = document.cookie.split('; ').map(c => {
            const keyVal = c.split('=')
            return { [keyVal[0]]: keyVal[1] }
        })
        const cookie = cookies.filter(c => typeof c[cookieName] !== 'undefined')
        return cookie && cookie[0][cookieName]
    },
    create: (name, value, days) => {
        let expires
        if (days) {
            const date = new Date()
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000))
            expires = "; expires=" + date.toGMTString()
        } else {
            expires = ""
        }
        document.cookie = `${name}=${value + expires}; path=/`
    }
};
(() => {
    const selectors = document.querySelectorAll('[data-wpte-currency]')
    const handleCurrencySelection = select => e => {
        if(select) {
            wpteccCookie.create('wptecc-user-currency', e.target.value)
            window.location.reload()
        }
    }
    selectors && selectors.forEach(el => {
        if (el.tagName.toLowerCase() === 'select') {
            el.addEventListener('change', handleCurrencySelection(true))
        } else {
            el.addEventListener('click', handleCurrencySelection()) // Useful other elements except select.
        }
    })
})()
