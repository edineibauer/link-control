(function () {
    'use strict';

    var initialData = "Olá Mundo";

    var app = {
        content: $("#content"),
        lib: $("#content").attr("data-lib"),
        file: $("#content").attr("data-file"),
        isLoading: true,
        spinner: document.querySelector('.loader')
    };

    /*****************************************************************************
     *
     * Methods to update/refresh the UI
     *
     ****************************************************************************/

    // Updates content app
    app.updateContent = function (data) {
        if ($.isArray(data) && (typeof (data.template) !== "undefined" || typeof (data[0].template) !== "undefined")) {
            if (typeof (data.template) !== "undefined") {
                app.content.template(data.template, data);
            } else {
                $.each(data, function (i, e) {
                    app.content.template(e.template, e);
                })
            }
        } else {
            app.content.html(data);
        }

        if (app.isLoading) {
            app.spinner.setAttribute('hidden', true);
            app.content.removeClass('hide');
            app.isLoading = false;
        }
    };


    /*****************************************************************************
     *
     * Methods for dealing with the model
     *
     ****************************************************************************/

    app.getRequestData = function (url, folder, tipo, data) {
        if ('caches' in window) {
            /*
             * Check if the service worker has already cached this data.
             * If the service worker has the data, then display the cached
             * data while the app fetches the latest data.
             */
            caches.match(url).then(function (response) {
                if (response) {
                    console.log(response);
                    response.json().then(function updateFromCache(json) {
                        app.updateContent(json);
                    });
                }
            });
        } else {
            if (data)
                app.updateContent(data);
            else
                app.updateContent(initialData);
        }

        var fileUp = (typeof (tipo) === "undefined" ? "view/" + app.file : "dobra/" + app.file);
        post(app.lib, fileUp, {}, function (g) {
            app.updateContent(g);
            db.set(app.lib + "-" + app.file + "-" + folder, {data: g});

            if(typeof (tipo) === "undefined")
                db.get(app.lib + "-" + app.file + "-" + 'dobra').then(val => checkCacheData(val, 'dobra', 1));
        });
    };

    // TODO add startup code here
    /************************************************************************
     *
     * Code required to start the app
     *
     * OBSERVAÇÃO: To simplify this codelab, we've used localStorage.
     *   localStorage is a synchronous API and has serious performance
     *   implications. It should not be used in production applications!
     *   Instead, check out IDB (https://www.npmjs.com/package/idb) or
     *   SimpleDB (https://gist.github.com/inexorabletash/c8069c042b734519680c)
     ************************************************************************/

    function checkCacheData(val, folder, tipo) {
        if (val === undefined) {
            app.getRequestData(app.lib + "-" + app.file + "-" + folder, folder, tipo);
        } else {
            app.getRequestData(val.url, folder, tipo, val.data);
        }
    }

    db.get(app.lib + "-" + app.file + "-" + 'view').then(val => checkCacheData(val, 'view'));

    // TODO add service worker code here
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker
            .register(HOME + 'service-worker.js')
            .then(function () {
                console.log('Service Worker Registered');
            });
    }
})();
