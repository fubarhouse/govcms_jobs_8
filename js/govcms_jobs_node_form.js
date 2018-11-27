(function ($, Drupal) {
  Drupal.behaviors.govcmsJobsNodeFormSelect2 = {
    attach: function attach(context, setting) {
      $('#edit-field-agencies', context).select2({
        width: '440px',
        minimumResultsForSearch: 10
      });
      $('#edit-field-job-categories', context).select2({
        width: '440px',
        minimumResultsForSearch: 10
      });
    }
  };
})(jQuery, Drupal);
