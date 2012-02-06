jQuery.fn.outerHTML = function(s) {
    return (s)
    ? this.before(s).remove()
    : jQuery("<p>").append(this.eq(0).clone()).html();
}

jQuery.fn.placeholder = function(){
    var $this = $(this),
        placeholder = $this.attr('placeholder');

    if($this.val() == ''){ 
        $this.val(placeholder).addClass('placeholder'); 
    };
    

    $this.focus(function(){
        var $this = $(this),
            placeholder = $this.attr('placeholder');

        if($this.val() == placeholder){
            $this.removeClass('placeholder').addClass('input-active');
            $this.val('');
        }
    }).blur(function(){
        var $this = $(this),
            placeholder = $this.attr('placeholder');

        $this.removeClass('input-active');
        
        if($this.val() == ''){
            $this.val(placeholder);
            $this.addClass('placeholder');
        }            
    });

    return $this;
}

jQuery.fn.checkDefaultClass = function(val){
/*    var $cloned = $(this).clone();
    $cloned.removeClass(val).removeClass('no-default');
    if($cloned.attr('class') == 'undefined'){
        return true
    }else{
        if($cloned.attr('class').length == 0)
            return true;
        else
            return false;
    }*/
    $cloned = $(this).clone();
    $cloned.removeClass(val).removeClass('no-default');
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
