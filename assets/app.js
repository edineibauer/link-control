(function () {
    'use strict';

    var app = {
        content: $("#content"),
        url: '',
        lib: $("#content").attr("data-lib"),
        file: $("#content").attr("data-file"),
        isLoading: true,
        spinner: $('.loader')
    };

    app.reloadUrl = function(url) {
        app.isLoading = true;
        app.spinner.removeClass("hide");
        app.content.addClass("opacity");
        setTimeout(function () {
            app.url = "";
            app.getUrl(url);
        },500);
    }

    app.getUrl = function (url) {
        if(app.url === "" || app.url !== url) {
            if (!app.isLoading) {
                app.isLoading = true;
                app.spinner.removeClass("hide");
            }
            app.url = url;
            app.file = url === HOME || url + "/" === HOME ? "index" : url.replace(HOME, "");
            history.pushState(null, null, url);
            app.content.attr("data-load", "1").addClass("opacity");
            app.loadStyleUrl();
            app.getRequestData('view');

        } else if(!app.isLoading){
            app.reloadUrl(url);
        }
    }

    app.getRequestData = function (folder) {
        get(folder + "/" + app.file, function (g) {
            if(g) {
                app.updateContent(g.content);

                if (folder === "view") {
                    $("title").text(g.title);
                    app.lib = g.lib;
                    app.loadScriptUrl();
                    app.getRequestData('dobra');
                } else if (app.isLoading) {
                    app.spinner.addClass('hide');
                    app.isLoading = false;
                }
            } else {
                if (folder === "view") {
                    app.content.html("").attr("data-load", "0").removeClass("opacity");
                    app.loadScriptUrl();
                    app.getRequestData('dobra');
                } else if (app.isLoading) {
                    app.spinner.addClass('hide');
                    app.isLoading = false;
                }
            }
        });
    };

    app.loadStyleUrl = function() {
        let css = HOME + (ISDEV && app.lib === DOMINIO ? "" : "vendor/conn/"+ app.lib +"/") + "assets/" + app.file + ".css";
        let $head = $("head");
        if(!$head.find("link[href='"+ css +"?v=" + VERSION + "']").length)
            $head.template("style", {"href": css, "version": VERSION});
    }

    app.loadScriptUrl = function() {
        let js = HOME + (ISDEV && app.lib === DOMINIO ? "" : "vendor/conn/"+ app.lib +"/") + "assets/" + app.file + ".js";
        let $head = $("head");
        if(!$head.find("script[src='"+ js +"?v=" + VERSION + "']").length)
            $head.template("script", {"src": js, "version": VERSION});
    }

    // Updates content app, gerencia motor de templates
    app.updateContent = function (data) {
        if(typeof (data) !== "undefined") {
            if ($.isArray(data) || typeof (data.template) !== "undefined") {
                if (typeof (data.template) !== "undefined") {
                    app.content.template(data.template, data);
                } else {
                    $.each(data, function (i, e) {
                        app.updateContent(e);
                    });
                }
            } else {
                if (app.content.attr("data-load") === '1')
                    app.content.html(data).attr("data-load", "0").removeClass("opacity");
                else
                    app.content.append(data);
            }
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
        app.lib = $(this).attr("data-lib") ? $(this).attr("data-lib") : app.content.attr("data-lib");

        history.pushState(null, null, url);
        app.getUrl(url);
    });
    app.getUrl(HOME);
})();
