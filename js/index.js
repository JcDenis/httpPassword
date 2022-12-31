/*global $, dotclear */
'use strict';

$(function () {
  $('#section_menu input[type=submit]').hide();
  $('#section_menu #part').on('change', function () {this.form.submit();});

  $('.checkboxes-helpers').each(function () {
    dotclear.checkboxesHelpers(this, undefined, '#form-records td input[type=checkbox]', '#form-records #del-action');
  });
  $('#form-records td input[type=checkbox]').enableShiftClick();
  dotclear.condSubmit('#form-records td input[type=checkbox]', '#form-records #del-action');
});