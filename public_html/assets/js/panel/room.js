var editor = new Jodit("#editor", {
    "uploader": {
        "insertImageAsBase64URI": true
    },
    "spellcheck": false,
    "allowResizeY": true,
    "toolbarSticky": false,
    uploader: {
        url: updateUrl + '/upload-image'
    },
    filebrowser: {
        ajax: {
            url: 'http://localhost:8181/index-test.php'
        }
    }
});
document.getElementsByClassName("jodit_wysiwyg")[0].setAttribute('autocomplete', 'off');
document.getElementsByClassName("jodit_wysiwyg")[0].setAttribute('autocorrect', 'off');
document.getElementsByClassName("jodit_wysiwyg")[0].setAttribute('autocapitalize', 'off');
document.getElementsByClassName("jodit_wysiwyg")[0].setAttribute('spellcheck', 'false');
if (roomDisabled)
    document.getElementsByClassName("jodit_wysiwyg")[0].setAttribute('contenteditable', "false");

$(document).on('click', '.others-card', openNote);
$(document).on('click', '.new-note-btn', newNote);
$(document).on('click', '.note-remover', removeNote);
$(document).on('input', '#main-note-title', updateNote);
$(document).on('input', '.room-edit-name', updateRoomName);
$(document).on('click', '.search-user-btn btn', addMember);
$(document).on('click', '.user-member-remove', removeMember);
$(document).on('click', '.user-file-remove', removeFile);
$(document).on('click', '.user-file-copy', copyFile);

document.getElementsByClassName("jodit_wysiwyg")[0].addEventListener('input', () => {
    var el = document.getElementById("main-note-title");
    var old = editor.getEditorValue();
    setTimeout(function () {
        if (old == editor.getEditorValue()) {
            $.ajax({
                url: updateUrl + '/' + el.parentNode.getElementsByClassName("note-id-class")[0].innerText + '/content',
                type: "POST",
                dataType: "json",
                data: {
                    "content": old
                },
                async: true,
                success: function (data) {
                    $('.other-row').prepend($('.card-hide').parent());
                    $("#main-note-edited").html(data.edited);
                    if (data.status !== true) {
                        for (let messege of data.messages) {
                            appShowNotification(messege[0], messege[1]);
                        }
                    }

                }
            });
        }
    }, 500);
});

function copyFile(e) {
    var el = e.currentTarget;
    var furl = el.getAttribute("f-url");
    copyToClipboard(furl);
}

function copyToClipboard(text) {
    window.prompt("Copy to clipboard: Ctrl+C, Enter", text);
  }

function removeMember(e) {
    var el = e.currentTarget.parentNode.parentNode;
    var name = el.getAttribute("user-member");
    $.ajax({
        url: updateUrl + '/remove-member',
        type: "POST",
        dataType: "json",
        data: {
            "username": name
        },
        async: true,
        success: function (data) {
            if (data.status !== true) {
                for (let messege of data.messages) {
                    appShowNotification(messege[0], messege[1]);
                }
            } else {
                el.parentNode.remove();
            }
        }
    });
}
function removeFile(e) {
    var el = e.currentTarget;
    var fid = el.getAttribute("f-id");
    $.ajax({
        url: updateUrl + '/remove-file/' + fid,
        type: "POST",
        dataType: "json",
        async: true,
        success: function (data) {
            if (data.status !== true) {
                for (let messege of data.messages) {
                    appShowNotification(messege[0], messege[1]);
                }
            } else {
                el.parentNode.parentNode.parentNode.remove();
            }
        }
    });
}

function addMember(e) {
    if (e.keyCode === undefined || e.keyCode == 13) {
        var name = document.getElementById("search-user-input").value;
        $.ajax({
            url: updateUrl + '/add-member',
            type: "POST",
            dataType: "json",
            data: {
                "username": name
            },
            async: true,
            success: function (data) {
                if (data.status !== true) {
                    for (let messege of data.messages) {
                        appShowNotification(messege[0], messege[1]);
                    }
                } else {
                    document.getElementById("search-user-input").value = "";
                    document.querySelector(".add-user-label").innerHTML = "Send";
                }
            }
        });
    }
}

function updateRoomName(e) {
    var el = e.currentTarget;
    var old = el.innerText;
    setTimeout(function () {
        if (old == el.innerText) {
            $.ajax({
                url: updateUrl + '/update-room',
                type: "POST",
                dataType: "json",
                data: {
                    "name": old
                },
                async: true,
                success: function (data) {
                    if (data.status !== true) {
                        for (let messege of data.messages) {
                            appShowNotification(messege[0], messege[1]);
                        }
                        document.querySelector(".room-edit-name").innerHTML = document.querySelector(".room-edit-name").getAttribute("room-name");
                    } else {
                        document.querySelector(".room-edit-name").setAttribute("room-name", old);
                    }
                }
            });
        }
    }, 500);
}

function removeNote(e) {
    var el = e.currentTarget;
    var noteId = el.getAttribute('note-id');
    $.ajax({
        url: updateUrl + '/' + noteId + '/remove',
        type: "POST",
        async: true,
        success: function (data) {
            if (data.status !== true) {
                for (let messege of data.messages) {
                    appShowNotification(messege[0], messege[1]);
                }
            } else {
                el.parentNode.getElementsByClassName("others-card")[0].animate([
                    { // from
                        height: el.parentNode.getElementsByClassName("others-card")[0].offsetHeight + "px",
                        marginBottom: "20px"
                    },
                    { // to
                        height: "0px",
                        marginBottom: "0px"
                    }
                ], timeToanimate);
                setTimeout(() => {
                    el.parentNode.remove();
                }, timeToanimate);
            }
        }
    });
}
function newNote(e) {
    e.preventDefault();
    $.ajax({
        url: updateUrl + '/new-note',
        type: "POST",
        async: true,
        success: function (data) {
            if (data.status !== true) {
                for (let messege of data.messages) {
                    appShowNotification(messege[0], messege[1]);
                }
            } else {
                var temp = document.querySelector(".other-row > div:not(.col-note-hide) .others-card");
                var toHe = (temp !== null ? temp.offsetHeight : '101') + "px";
                var chide = document.getElementsByClassName("card-hide")[0];
                $('.other-row').prepend($('.card-hide').first().parent().clone());
                var newEl = document.getElementsByClassName("card-hide")[0];
                chide.getElementsByTagName('h4')[0].innerHTML = $("#main-note-title").html();
                chide.getElementsByClassName('others-note-edited')[0].innerHTML = $("#main-note-edited").html();
                chide.getElementsByClassName('others-note-autor')[0].innerHTML = $("#main-note-autor").html();
                chide.getElementsByClassName('others-note-content')[0].innerHTML = document.getElementsByClassName("jodit_wysiwyg")[0].innerHTML;

                newEl.getElementsByClassName('others-note-id')[0].innerHTML = data.note.id;
                newEl.parentNode.getElementsByClassName('note-remover')[0].setAttribute('note-id', data.note.id);
                newEl.getElementsByTagName('h4')[0].innerHTML = data.note.title;
                newEl.getElementsByClassName('others-note-autor')[0].innerHTML = data.note.autor;
                newEl.getElementsByClassName('others-note-content')[0].innerHTML = data.note.content;
                newEl.getElementsByClassName('others-note-edited')[0].innerHTML = data.note.edited;

                $("#note-id").html(data.note.id);
                $("#main-note-title").html(data.note.title);
                $("#main-note-autor").html(data.note.autor);
                $("#main-note-edited").html(data.note.edited);
                document.getElementsByClassName("jodit_wysiwyg")[0].innerHTML = data.note.content;
                chide.parentNode.style.display = "block";
                chide.parentNode.classList.remove("col-note-hide");
                document.getElementsByClassName("other-row")[0].style.marginBottom = "0";
                chide.animate([
                    { // from
                        height: "0"
                    },
                    {
                        height: toHe
                    }
                ], timeToanimate);
                chide.style.height = toHe;
                chide.classList.remove("card-hide");
                document.getElementsByClassName("other-row")[0].style.marginBottom = "20px";
            }
        }
    });
}


function updateNote(e) {
    var el = document.getElementById("main-note-title");
    var old = el.innerText;
    setTimeout(function () {
        if (old == el.innerText) {
            $.ajax({
                url: updateUrl + '/' + el.parentNode.getElementsByClassName("note-id-class")[0].innerText + '/update',
                type: "POST",
                dataType: "json",
                data: {
                    "title": old
                },
                async: true,
                success: function (data) {
                    $('.other-row').prepend($('.card-hide').parent());
                    $("#main-note-edited").html(data.edited);
                    if (data.status !== true) {
                        for (let messege of data.messages) {
                            appShowNotification(messege[0], messege[1]);
                        }
                    }

                }
            });
        }
    }, 1000);
}
var timeToanimate = 300;
function openNote(e) {
    var cele = e.currentTarget;
    var bouto = document.getElementsByClassName("card")[0].getBoundingClientRect();
    var boufrom = cele.getBoundingClientRect();
    var boxfrom = {
        "top": boufrom.top + window.pageYOffset,
        "left": boufrom.left,
        "width": boufrom.width,
        "height": boufrom.height
    };
    var boxto = {
        "top": bouto.top + window.pageYOffset,
        "left": bouto.left,
        "width": bouto.width,
        "height": bouto.height
    };
    var mcard = document.getElementById('moving-card');
    mcard.style.display = "block";
    mcard.getElementsByClassName("others-note-autor")[0].innerHTML = cele.getElementsByClassName('others-note-autor')[0].innerHTML;
    mcard.getElementsByClassName("others-note-edited")[0].innerHTML = cele.getElementsByClassName('others-note-edited')[0].innerHTML;
    mcard.getElementsByClassName("others-note-content")[0].innerHTML = cele.getElementsByClassName('others-note-content')[0].innerHTML;
    mcard.getElementsByTagName("h4")[0].innerHTML = cele.getElementsByTagName('h4')[0].innerHTML;
    mcard.style.opacity = "0";
    var hihight = cele.offsetHeight;
    var chide = document.getElementsByClassName("card-hide")[0];
    chide.getElementsByTagName('h4')[0].innerHTML = $("#main-note-title").html();
    chide.getElementsByClassName('others-note-edited')[0].innerHTML = $("#main-note-edited").html();
    chide.getElementsByClassName('others-note-autor')[0].innerHTML = $("#main-note-autor").html();
    chide.getElementsByClassName('others-note-content')[0].innerHTML = document.getElementsByClassName("jodit_wysiwyg")[0].innerHTML;
    // chide.getElementsByClassName('others-note-id')[0].innerHTML = $("#note-id").html();
    chide.parentNode.style.display = "block";
    document.getElementsByClassName("other-row")[0].style.marginBottom = "0";
    chide.animate([
        { // from
            height: "0"
        },
        {
            height: cele.offsetHeight + "px"
        }
    ], timeToanimate);
    cele.animate([
        { // from
            height: hihight + "px"
        },
        { // to
            height: "0"
        }
    ], timeToanimate);
    mcard.animate([
        { // from
            top: boxfrom.top + "px",
            left: boxfrom.left + "px",
            width: boxfrom.width + "px",
            height: boxfrom.height + "px",
            opacity: 1
        },
        {
            opacity: 1,
            offset: 0.7
        },
        { // to
            top: boxto.top + "px",
            left: boxto.left + "px",
            width: boxto.width + "px",
            height: boxto.height + "px",
            opacity: 0
        }
    ], timeToanimate);
    cele.parentNode.classList.add('col-note-hide');
    cele.classList.add('card-hide');
    chide.parentNode.classList.remove("col-note-hide");
    chide.classList.remove("card-hide");
    setTimeout(function () {
        cele.style.height = "0";
        chide.style.height = hihight + "px";
        cele.parentNode.style.display = "none";
        document.getElementsByClassName("other-row")[0].style.marginBottom = "20px";
        $("#main-note-title").html(cele.getElementsByTagName('h4')[0].innerHTML);
        $("#main-note-edited").html(cele.getElementsByClassName('others-note-edited')[0].innerHTML);
        $("#main-note-autor").html(cele.getElementsByClassName('others-note-autor')[0].innerHTML);
        $("#note-id").html(cele.getElementsByClassName('others-note-id')[0].innerHTML);
        document.getElementsByClassName("jodit_wysiwyg")[0].innerHTML = cele.getElementsByClassName('others-note-content')[0].innerHTML;
        mcard.style.display = "none";
    }, timeToanimate);
    // mcard.style.top = boxto.top + "px";
    // mcard.style.left = boxto.left + "px";
    // mcard.style.width = boxto.width + "px";
    // mcard.style.height = boxto.height + "px";
}