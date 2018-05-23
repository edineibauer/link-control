(function () {
    'use strict';

    var app = {
        content: $("#content"),
        lib: $("#content").attr("data-lib"),
        file: $("#content").attr("data-file"),
        isLoading: true,
        spinner: $('.loader')
    };

    app.clearContent = function () {
        if (!app.isLoading) {
            app.content.html("").addClass("hide");
            app.isLoading = true;
            app.spinner.removeClass("hide");
        }
    }

    app.getUrl = function (url) {
        app.clearContent();
        app.file = url === HOME || url + "/" === HOME ? "index" : url.replace(HOME, "");

        history.pushState(null, null, url);
        app.getRequestData(url, 'view');
    }

    app.getRequestData = function (url, folder) {
        get(app.lib, folder + "/" + app.file, function (g) {
            app.updateContent(g);

            if (folder === "view")
                app.getRequestData(url.replace('/view/', '/dobra/'), 'dobra');
        });
    };

    // Updates content app, gerencia motor de templates
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
            app.content.append(data);
        }

        if (app.isLoading) {
            app.spinner.addClass('hide');
            app.content.removeClass('hide');
            app.isLoading = false;
        }
    };

    // TODO add service worker code here
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker
            .register(HOME + 'service-worker.js')
            .then(function () {
                console.log('Service Worker Registered');
            });
    }

    $("a").off("click").on("click", function (e) {
        e.preventDefault();
        let url = $(this).attr("href");
        if($(this).attr("data-lib"))
            app.lib = $(this).attr("data-lib");

        history.pushState(null, null, url);
        app.getUrl(url);
    });
    app.getUrl(HOME);
})();