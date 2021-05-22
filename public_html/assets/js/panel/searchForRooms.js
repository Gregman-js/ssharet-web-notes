$(document).on('input', '#search-for-rooms', searchRooms);

function searchRooms(e) {
    var name = e.currentTarget.value;
    console.log(baseUrl + "search-for-rooms");
    console.log(name);
    var res = document.querySelector(".search-rooms-result");
    res.style.opacity = "0.5";
    $.ajax({
        url: baseUrl + "search-for-rooms",
        type: "POST",
        dataType: "json",
        data: {
            "value": name
        },
        async: true,
        success: function (data) {
            if (name.length == 0 || data.rooms.length > 0)
                res.innerHTML = "";
            else
                res.innerHTML = '<div style="text-align: center; color: #aaa;">No results</div>';
            if (data.status) {
                var rooms = data.rooms;
                for (var i = 0; i < rooms.length; i++) {
                    if (rooms[i].your)
                        res.innerHTML += `<div class="card" style="overflow: hidden;"><a href="` + ssharetUrl + "room/" + rooms[i].url + `" style="text-decoration: none; color: inherit;">
                        <div class="card-body">
                        <div class="others-note-autor" style="font-size: 14px;">` + rooms[i].author + `</div>
                        <h5 class="m-0">` + rooms[i].name + `<span style="color: #aaa; font-size: 13px;"> <span class="go-to-url"><i class="fas fa-external-link-alt"></i> ` + rooms[i].url + `</span></span></h5>
                        <div class="others-note-edited" style="text-align: right; font-size: 12px;">` + rooms[i].edited + `</div>
                        <div class="your-note" style="display: block">
                            <div class="your-note-in">Your room</div>
                        </div>
                        </div></a>
                    </div>`;
                }
                for (var i = 0; i < rooms.length; i++) {
                    if (!rooms[i].your)
                        res.innerHTML += `<div class="card" style="overflow: hidden;"><a href="` + ssharetUrl + "room/" + rooms[i].url + `" style="text-decoration: none; color: inherit;">
                        <div class="card-body">
                        <div class="others-note-autor" style="font-size: 14px;">` + rooms[i].author + `</div>
                        <h5 class="m-0">` + rooms[i].name + `<span style="color: #aaa; font-size: 13px;">(` + rooms[i].url + `)</span></h5>
                        <div class="others-note-edited" style="text-align: right; font-size: 12px;">` + rooms[i].edited + `</div>
                        </a>
                    </div>`;
                }
            }
            res.style.opacity = "1";
        }
    });
}