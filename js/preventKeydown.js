//prevent mysql chars on input
$("input").keydown(function(e){

    switch(e.keyCode) {
        case 219:
        case 220:
        case 221:
        case 222:
            e.preventDefault();
            break;
        default:
            // code block
    }
});