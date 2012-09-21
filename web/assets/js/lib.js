jQuery.fn.outerHTML = function(s) {
    return (s)
    ? this.before(s).remove()
    : jQuery("<p>").append(this.eq(0).clone()).html();
}

String.prototype.camelCase = function() {
    str = this;
    return str
        .replace(/[\s\-](.)/g, function($1) { return $1.toUpperCase(); })
        .replace(/[\s\-]/g, '')
        .replace(/^(.)/, function($1) { return $1.toLowerCase(); });
}

function getParameterByName(name){
  name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
  var regexS = "[\\?&]" + name + "=([^&#]*)";
  var regex = new RegExp(regexS);
  var results = regex.exec(window.location.href);
  if(results == null)
    return "";
  else
    return decodeURIComponent(results[1].replace(/\+/g, " "));
}
