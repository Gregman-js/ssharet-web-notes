$(document).on('click', '.file-remover', removeFile);

function removeFile(e) {
    var el = e.currentTarget;
    var fid = el.getAttribute("f-id");
    $.ajax({
        url: baseUrl + 'remove-file/' + fid,
        type: "POST",
        dataType: "json",
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