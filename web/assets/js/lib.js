jQuery.fn.outerHTML = function(s) {
    return (s)
    ? this.before(s).remove()
    : jQuery("<p>").append(this.eq(0).clone()).html();
}

jQuery.fn.placeholder = function(){
    var $this = $(this),
        placeholder = $this.attr('data-placeholder');

    $this.val(placeholder);
    $this.addClass('placeholder');

    
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

        if($this.val() == ''){
            $this.removeClass('input-active');
            $this.val(placeholder);
            $this.addClass('placeholder');
        }            
    });

    return $this;
}