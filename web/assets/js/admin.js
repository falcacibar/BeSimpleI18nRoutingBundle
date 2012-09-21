$(document).ready(function(){
    // Configuración de datepicker
    $.datepicker.setDefaults( $.datepicker.regional[ "es" ] );
    var dates = $( "#fecha-desde-ph, #fecha-hasta-ph" ).datepicker({
        defaultDate: "+1w",
        changeMonth: true,
        changeYear: true,
        yearRange: '2009:c',
        numberOfMonths: 1,
        onSelect: function( selectedDate ) {
            if(this.id == 'fecha-desde-ph'){
                var option = 'minDate';
            }else if(this.id == 'fecha-hasta-ph'){
                var option = 'maxDate';
            }

            var instance = $( this ).data( "datepicker" ),
                date = $.datepicker.parseDate(
                    instance.settings.dateFormat ||
                    $.datepicker._defaults.dateFormat,
                    selectedDate, instance.settings );
            dates.not( this ).datepicker( "option", option, date );
        },
        dateFormat: 'dd-mm-yy',
    });

    $( "#fecha-desde-ph" ).datepicker( "option", "altFormat", "dd-mm-yy" );
    $( "#fecha-desde-ph" ).datepicker( "option", "altField", '#fecha-desde' );

    $( "#fecha-hasta-ph" ).datepicker( "option", "altFormat", "dd-mm-yy" );
    $( "#fecha-hasta-ph" ).datepicker( "option", "altField", '#fecha-hasta' );

    // Configuración de timepicker$('table').eq(1)
    var datetimes = $( "#fecha-desde-c-ph, #fecha-hasta-c-ph" ).datetimepicker({
        defaultDate: "+1w",
        changeMonth: true,
        changeYear: true,
        yearRange: '2009:c',
        numberOfMonths: 1,
        onSelect: function( selectedDate ) {
            if(this.id == 'fecha-desde-c-ph'){
                var option = 'minDate';
            }else if(this.id == 'fecha-hasta-c-ph'){
                var option = 'maxDate';
            }
            console.log($(this).data( "datepicker" ));
            var instance = $( this ).data( "datepicker" ),
                date = $.datepicker.parseDate(
                    instance.settings.dateFormat ||
                    $.datepicker._defaults.dateFormat,
                    selectedDate, instance.settings );
            datetimes.not( this ).datepicker( "option", option, date );
        },
        dateFormat: 'dd-mm-yy',
    });

    $( "#fecha-desde-c-ph" ).datetimepicker( "option", "altFormat", "dd-mm-yy" );
    $( "#fecha-desde-c-ph" ).datetimepicker( "option", "altField", '#fecha-desde-c' );

    $( "#fecha-hasta-c-ph" ).datetimepicker( "option", "altFormat", "dd-mm-yy" );
    $( "#fecha-hasta-c-ph" ).datetimepicker( "option", "altField", '#fecha-hasta-c' );

    $('form').submit(function(){
        $(this).find('input, select').each(function(){
            $this = $(this);
            if($this.val() == ''){
                $this.attr('disabled', 'disabled');
            }
        });
    });

    $('.seleccionar-todo').click(function(e){
        e.preventDefault();
        $('input[name="id[]"]').click().attr('checked', 'checked');
    });

    $('.deseleccionar-todo').click(function(e){
        e.preventDefault();
        $('input[name="id[]"]').click().removeAttr('checked', 'checked');
    });

    $('input[type=checkbox]').change(function(){
        $this = $(this);
        if($this.is(':checked')){
            $this.parent().parent().addClass('selected_row');
        }else{
            $this.parent().parent().removeClass('selected_row');
        }
    });
});