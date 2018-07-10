(function ($) {

  Drupal.behaviors.nestedContent = {

    attach: function attach(context, settings) {
      $('.form-submit').on('mousedown', function () {
        // Make sure the ordering of weights gets saved properly.
        var $table = $('#nested-content-table');
        var $row = $table.find('tr.draggable');

        $row
            .parent()
            .find('tr.draggable')
            .each(function (index, value) {
              $(this).find('.nested-content-weight').val(index);
            });
      });
    }

  };

})(jQuery);
