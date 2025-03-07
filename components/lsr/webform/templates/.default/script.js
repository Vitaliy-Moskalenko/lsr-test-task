function change_lang() {
    var url = window.location.href;   console.log(url);
    var hash = location.hash;
    url = url.replace(hash, '');

    var lang = document.getElementsByName('lang')[0].value;

    if (url.indexOf('ui_lang' + "=") >= 0) {
        var prefix = url.substring(0, url.indexOf('ui_lang' + "="));
        var suffix = url.substring(url.indexOf('ui_lang' + "="));
        suffix = suffix.substring(suffix.indexOf("=") + 1);
        suffix = (suffix.indexOf("&") >= 0) ? suffix.substring(suffix.indexOf("&")) : "";
        url = prefix + 'ui_lang=' + lang + suffix;
    }
    else {
        url += (url.indexOf("?") < 0) ? '?ui_lang=' + lang : '&ui_lang=' + lang;
    }

    window.location.href = url + hash;
}

function replaceQueryParam(param, newval, search) {
    let regex = new RegExp("([?;&])" + param + "[^&;]*[;&]?");
    let query = search.replace(regex, "$1").replace(/&$/, '');
    return (query.length > 2 ? query + "&" : "?") + (newval ? param + "=" + newval : '');
}
function action_lang() {
    window.location = replaceQueryParam('user_lang', document.getElementsByName('lang')[0].value, window.location.search);
}