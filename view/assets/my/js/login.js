var recoveryFree = true;
var loginFree = true;
var novaSenha = true;
var logoutFree = true;

function recoveryEmail() {
    if (recoveryFree) {
        recoveryFree = false;
        $.post(HOME + 'vendor/conn/session-control/src/SessionControl/request/recoveryEmail.php', {email: $("#recovery-email").val()}, function (g) {
            if (g === "1") {
                $("#recovery-email").val("");
                Materialize.toast('Link de Recuperação enviada ao email', 4000);
            } else if (g === "2") {
                Materialize.toast("Email não encontrado!", 4000);
            } else {
                Materialize.toast("Erro Desconhecido", 3000);
                console.log(g);
            }
            recoveryFree = true;
        });
    }
}

function newPassword() {
    if (novaSenha) {
        novaSenha = false;
        if ($("#nova-senha").val() === $("#nova-senha-confirm").val()) {
            $.post(HOME + 'vendor/conn/session-control/src/SessionControl/request/setNewPassword.php', {senha: $("#nova-senha").val(), code: $("#code").val()}, function (g) {
                if (g === "1") {
                    Materialize.toast('Senha Modificada, redirecionando...', 2000);
                    setTimeout(function () {
                        window.location.href = HOME + "login";
                    },2000);
                } else if (g === "2") {
                    Materialize.toast("Token de alteração Inválido! Favor, requisitar email de recuperação.", 6000);
                } else {
                    Materialize.toast("Erro Desconhecido", 3000);
                    console.log(g);
                }
                novaSenha = true;
            });
        } else {
            novaSenha = true;
        }
    }
}

function login() {
    if (loginFree) {
        loginFree = false;
        $.post(HOME + 'vendor/conn/session-control/src/SessionControl/request/login.php', {
            email: $("#emaillog").val(),
            pass: $("#passlog").val(),
            recaptcha: $("#g-recaptcha-response").val()
        }, function (g) {
            g = JSON.parse(g);
            if (g['status'] === "1") {
                window.location.href = HOME + 'login';
            } else if (g['status'] === "2") {
                setTimeout(function () {
                    Materialize.toast(g['mensagem'], 2500);
                }, 2500);
            } else {
                Materialize.toast("Erro Desconhecido", 3000);
                console.log(g);
            }
            loginFree = true;
        });
    }
}

function logout() {
    if (logoutFree) {
        logoutFree = false;

        $.post(HOME + 'vendor/conn/session-control/src/SessionControl/request/logout.php', function (g) {
            g = JSON.parse(g);
            if (g['status'] === "1") {
                Materialize.toast(g['mensagem'], 2000);
                setTimeout(function () {
                    window.location.href = HOME;
                },2000);

            } else if (g['status'] === "2") {
                Materialize.toast(g['mensagem'], 3000);
            } else {
                Materialize.toast("Erro Desconhecido", 3000);
                console.log(g);
            }
            logoutFree = true;
        });
    }
}