(() => {
    let trigger = document.querySelector('.trigger');
    const apiSettings = wpApiSettings;

    /**
     * Rest call.
     *
     * @param url
     * @param data
     * @returns {Promise<any>}
     */
    async function postData(url = '', data = {}) {
        const response = await fetch(url, {
            method: 'POST',
            mode: 'cors',
            cache: 'no-cache',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': apiSettings.nonce
            },
            body: JSON.stringify(data)
        });

        return response.text();
    }

    /**
     * Fetches PDF with REST call.
     * Triggers PDF download.
     * @param e
     */
    function loadPDF(e) {
        let data = {};
        data.nonce_local = apiSettings.nonce_local;
        let link = document.createElement('a');

        postData(apiSettings.root + 'wpsnippets/v2/restmpdf', data)
            .then((restResponse) => {
                let a = document.createElement('a');
                let d = new Date().toDateString();

                a.href = "data:application/pdf;base64," + restResponse;
                a.download = 'pdf-' + d + '.pdf';

                console.log(a);
                a.dispatchEvent(new MouseEvent('click'));
            });
    }

    trigger.addEventListener('click', (e) => {
        loadPDF(e);
    });
})();