var elem;
window.addEventListener('DOMContentLoaded', (event) => {
    elem = document.getElementById('room_password');
    elem.addEventListener('input', passShow);
    document.getElementsByClassName('url-copy')[0].addEventListener('click', () => {
        var copyText = document.getElementsByClassName('url-suffix-inp')[0];
        copyText.select();
        document.execCommand("copy");
    });

    var input = document.getElementsByClassName('form-group label-floating');
    for(var i = 0; i < input.length; i++){
        var ele = input[i].getElementsByTagName('input')[0];
        if(ele.type == 'text' && ele.name.includes('room') && ele.value.length > 0)
            input[i].classList.remove('is-empty');
    }

});
function passShow() {
    var mm = document.getElementsByClassName('vpn-key')[0];
    if(elem.value.length > 0) {
        var valu = elem.value.length > 6 ? 6 : elem.value.length;
        console.log(scale(valu, 1, 6, 0, 1));
        console.log(getColor(scale(valu, 1, 6, 0, 1)));
        mm.style.color = getColor(scale(valu, 1, 6, 0, 1));
    } else 
    {
        mm.style.color = "#555";
    }
}
function getColor(value){
    //value from 0 to 1
    var hue=((value)*120).toString(10);
    return ["hsl(",hue,",100%,50%)"].join("");
}
const scale = (num, in_min, in_max, out_min, out_max) => {
    return (num - in_min) * (out_max - out_min) / (in_max - in_min) + out_min;
  }