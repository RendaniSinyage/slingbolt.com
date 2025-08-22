$(document).on('submit', 'form[data-ajax-form="true"]', function(e) {
    e.preventDefault();
    var form = $(this);
    var url = form.attr('action');
    var method = form.attr('method');
    var data = form.serialize();
    var select_id = form.data('select-id');
    var modal_id = form.closest('.modal').attr('id');

    if (!modal_id) {
        modal_id = 'commonModal'; // Fallback to default modal
    }

    $.ajax({
        url: url,
        type: method,
        data: data,
        success: function(response) {
            if (response.success) {
                show_toastr('Success', response.message, 'success');
                $('#' + modal_id).modal('hide');

                if (select_id && response.data) {
                    var newOption = new Option(response.data.name, response.data.id, true, true);
                    var target_select;

                    if (select_id.startsWith('.')) {
                        // It's a class, target the last element in the repeater
                        target_select = $('[data-repeater-list]').find(select_id).last();
                    } else {
                        // It's an ID
                        target_select = $('#' + select_id);
                    }

                    if(target_select.length > 0){
                        target_select.append(newOption).trigger('change');
                    }
                }
            } else {
                show_toastr('Error', response.error || 'An unknown error occurred.', 'error');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            var errorMessage = 'An unknown error occurred.';
            if (jqXHR.responseJSON) {
                if (jqXHR.responseJSON.error) {
                    errorMessage = jqXHR.responseJSON.error;
                } else if (jqXHR.responseJSON.errors) {
                    var errors = jqXHR.responseJSON.errors;
                    // Display the first validation error
                    errorMessage = Object.values(errors)[0][0];
                }
            }
            show_toastr('Error', errorMessage, 'error');
        }
    });
});
