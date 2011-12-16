jQuery.fn.outerHTML = function(s) {
    return (s)
    ? this.before(s).remove()
    : jQuery("<p>").append(this.eq(0).clone()).html();
}

jQuery.fn.placeholder = function(){
    var $this = $(this),
        placeholder = $this.attr('data-placeholder');

    if($this.val() == ''){ 
        $this.val(placeholder).addClass('placeholder'); 
    };
    

    $this.focus(function(){
        var $this = $(this),
            placeholder = $this.attr('data-placeholder');

        if($this.val() == placeholder){
            $this.removeClass('placeholder').addClass('input-active');
            $this.val('');
        }
    }).blur(function(){
        var $this = $(this),
            placeholder = $this.attr('data-placeholder');

        $this.removeClass('input-active');
        
        if($this.val() == ''){
            $this.val(placeholder);
            $this.addClass('placeholder');
        }            
    });

    return $this;
}

jQuery.fn.checkDefaultClass = function(val){
    var $cloned = $(this).clone();
    $cloned.removeClass(val).removeClass('no-default').removeClass('caracteristica');
    if($cloned.attr('class').length == 0){
        return true;
    }else{
        return false;
    }
    
}