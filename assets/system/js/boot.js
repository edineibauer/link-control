function clickSend(id, event) {
    if (event.which === 13 && $("#" + id).length) {
        $("#" + id).trigger("onclick");
    }
}