/**
 * @file
 * Provides client-side validations for the Age Gate.
 */

var age_checker = {};

(function ($) {
  'use strict';
  Drupal.behaviors.age_checker = {
    attach: function (context, settings) {
      $(document).ready(function () {

        var agegate_bg = $('.age-checker-bg img').attr('src');
        $('#age_checker').css('background-image', 'url(' + agegate_bg + ')');
        $('.age-checker-bg').remove();

        if (screen.width < 480) {
          $('head').append('<meta name="viewport" content="width=device-width, initial-scale=1">');
        }
        if (window.location.pathname === '/age-gate' && drupalSettings.age_checker !== 'undefined') {
          var id1 = drupalSettings.age_checker.id_1;
          var id2 = drupalSettings.age_checker.id_2;
          var id3 = drupalSettings.age_checker.id_3;

          $(id1).keyup(function () {
            if (this.value.length === this.maxLength) {
              $(id2).focus();
            }
          });
          $(id2).keyup(function () {
            if (this.value.length === this.maxLength) {
              $(id3).focus();
            }
          });
        }

        $('#age_checker_country').change(function () {
          var selected_country = $(this).find('option:selected').val();
          var cookie_name = "country_selected=" + selected_country + "; path=/";
          document.cookie = cookie_name;
          location.reload();
        });
      });

      // Function to verify the age limits.
      age_checker.verify = function () {

        var now = new Date(drupalSettings.age_checker.currentdate);
        var date = now.getDate();
        var month = now.getMonth() + 1;
        var year = now.getFullYear();
        var age_checker_day = $("#age_checker_day").val();
        var age_checker_month = $("#age_checker_month").val();
        var age_checker_year = $("#age_checker_year").val();
        var age = year - age_checker_year;
        var dobdate = new Date(age_checker_year, age_checker_month - 1, age_checker_day).getTime();
        var today = now.getTime();
        var threshold_age = drupalSettings.age_checker.threshold_age;
        var day_placeholder = drupalSettings.age_checker.day_placeholder;
        var month_placeholder = drupalSettings.age_checker.month_placeholder;
        var year_placeholder = drupalSettings.age_checker.year_placeholder;
        var leapyear = ((age_checker_year % 4 === 0) && (age_checker_year % 100 !== 0)) || (age_checker_year % 400 === 0);
        var blank_err_message = drupalSettings.age_checker.blank_err_message;
        var dateformat_error = drupalSettings.age_checker.dateformat_error;
        var date_range_err_msg = drupalSettings.age_checker.date_range_err_msg;
        var remember_me = $('#age_checker_remember_me:checked').val();
        var destination = drupalSettings.age_checker.destination;

        if (age_checker_month > month) {
          age--;
        }
        else {
          if (age_checker_month === month && age_checker_day > date) {
            age--;
          }
        }

        if ((age_checker_month === '') ||
          (age_checker_day === '') ||
          (age_checker_year === '') ||
          (age_checker_month === month_placeholder) ||
          (age_checker_day === day_placeholder) ||
          (age_checker_year === year_placeholder)) {
          document.getElementById('age_checker_error_message').innerHTML = Drupal.t(blank_err_message);
          return false;
        }
        else if ((age_checker_year < 1900) || (age_checker_year > year)) {
          document.getElementById('age_checker_error_message').innerHTML = Drupal.t(date_range_err_msg);
          return false;
        }
        else if (age_checker_year.length !== 4) {
          document.getElementById('age_checker_error_message').innerHTML = Drupal.t(dateformat_error);
          return false;
        }
        else if ((age_checker_month < 1 || age_checker_month > 12)) {
          document.getElementById('age_checker_error_message').innerHTML = Drupal.t(dateformat_error);
          return false;
        }
        else if (age_checker_day < 1 || age_checker_day > 31) {
          document.getElementById('age_checker_error_message').innerHTML = Drupal.t(dateformat_error);
          return false;
        }
        else if ((age_checker_month === 4 || age_checker_month === 6 || age_checker_month === 9 || age_checker_month === 11) && age_checker_day === 31) {
          document.getElementById('age_checker_error_message').innerHTML = Drupal.t(dateformat_error);
          return false;
        }
        else if (age_checker_month === 2 && (age_checker_day === 29 && !leapyear || age_checker_day > 29)) {
          // Check for february 29th.
          document.getElementById('age_checker_error_message').innerHTML = Drupal.t(dateformat_error);
          return false;
        }
        else if (today - dobdate < 0) {
          document.getElementById('age_checker_error_message').innerHTML = Drupal.t(date_range_err_msg);
          return false;
        }
        else if (!parseInt(age_checker_month, 10) || !parseInt(age_checker_day, 10) || !parseInt(age_checker_year, 10)) {
          document.getElementById('age_checker_error_message').innerHTML = Drupal.t(dateformat_error);
          return false;
        }
        else if (age < threshold_age) {
          alert(drupalSettings.age_checker.under_age_err_msg);
          window.location = drupalSettings.age_checker.redirecturl;
        }
        else {
          var cookie_name = "age_checker=1; path=/;";
          document.cookie = cookie_name;
          if (remember_me === "1") {
            setCookie('remember_me', 1, drupalSettings.age_checker.cookie_expiration);
          }
          window.location = destination;
        }
        return true;
      };
    }
  };
})(jQuery);

function getCookie(cname) {
  'use strict';
  var name = cname + "=";
  var ca = document.cookie.split(';');
  for (var i = 0; i < ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) === ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) === 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}

function setCookie(cname, cvalue, exdays) {
  'use strict';
  var d = new Date();
  d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
  var expires = "expires=" + d.toUTCString();
  document.cookie = cname + "=" + cvalue + "; " + expires;
}
