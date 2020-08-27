(function () {
    var wpteccBlock = null
    var messageEl = null

    document.addEventListener('keyup', function (e) {
        if (typeof e.target.dataset.fixerApi === 'undefined')
            return
        e.target.dataset.state = 'dirty'
        if (messageEl) {
            messageEl.textContent = ''
        } else {
            messageEl = e.target.parentElement.querySelector('.wptecc-message')
            messageEl.textContent = ''
        }
    })
    document.addEventListener('focusout', function (e) {
        if (typeof e.target.dataset.fixerApi === 'undefined')
            return
        if (e.target.dataset.state === 'dirty' && e.target.value.length > 0) {
            messageEl.textContent = 'Loading...'
            // alert('content changed')
            if (window.fetch) {
                fetch(ajaxurl + '?action=wtecc_test&access_key=' + e.target.value)
                    .then(function (res) { return res.json() })
                    .then(function (result) {
                        if(!wpteccBlock) {
                            wpteccBlock = document.getElementById('wptecc-block')
                        }
                        if (result.success) {
                            messageEl.textContent = ''
                            var options = wpteccBlock.querySelectorAll('.wptecc-option')
                            options && options.forEach(function (el) {
                                el.removeAttribute('style')
                            })
                        } else {
                            if (result.data && result.data.code) {
                                if (result.data.code === 'invalid_access_key' && messageEl) {
                                    messageEl.textContent = result.data.message
                                } else {
                                    if (result.data.code === 'https_access_restricted') {
                                        var options = wpteccBlock.querySelectorAll('.wptecc-option')
                                        options && options.forEach(function (el) {
                                            el.removeAttribute('style')
                                        })
                                        messageEl.style.color = 'green'
                                        messageEl.textContent = 'Valid API Key and Free Subscription. Get Premium API Key to enjoy additonal features.'
                                    }
                                }
                            }
                        }
                    })
            }
            e.target.dataset.state = ''
        }
    })

    // Caching Purge.
    document.addEventListener('blur', function (e) {
        if (e.target.id !== 'wptecc-purge')
            return

        e.preventDefault()
        fetch && fetch(ajaxurl + '?action=wtecc_purge&_nonce=' + e.target.dataset.nonce)
            .then(function (res) {
                res.json()
                    .then(function (result) {
                        result.data && alert(result.data.message)
                    })
            })
    })
})();
