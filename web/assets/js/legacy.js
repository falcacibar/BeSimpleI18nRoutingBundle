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

$(document).ready(function(){
    $('input[type=text]').each(function(){ $(this).placeholder() });
});