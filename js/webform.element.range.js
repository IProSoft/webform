/**
 * @file
 * JavaScript behaviors for range element integration.
 */

(function ($, Drupal, once) {
  /**
   * Display HTML5 range output in a left/right aligned number input.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformRangeOutputNumber = {
    attach(context) {
      $(
        once('webform-range-output-number', '.js-form-type-range', context),
      ).each(function () {
        const $element = $(this);
        const $input = $element.find('input[type="range"]');
        const $output = $element.find('input[type="number"]');
        if (!$output.length) {
          return;
        }

        // Set output value.
        $output.val($input.val());

        // Sync input and output values.
        $input.on('change input', function () {
          $output.val($input.val());
        });
        $output.on('change input', function () {
          $input.val($output.val());
        });
      });
    },
  };

  /**
   * Display HTML5 range output in a floating bubble.
   *
   * @type {Drupal~behavior}
   *
   * @see https://css-tricks.com/value-bubbles-for-range-inputs/
   * @see https://stackoverflow.com/questions/33794123/absolute-positioning-in-relation-to-a-inputtype-range
   */
  Drupal.behaviors.webformRangeOutputBubble = {
    attach(context) {
      $(
        once('webform-range-output-bubble', '.js-form-type-range', context),
      ).each(function () {
        const $element = $(this);
        const $input = $element.find('input[type="range"]');
        const $output = $element.find('output');
        const display = $output.attr('data-display');

        if (!$output.length) {
          return;
        }

        $element.css('position', 'relative');

        $input
          .on('change input', function () {
            const inputValue = $input.val();

            // Set output text with prefix and suffix.
            const text =
              ($output.attr('data-field-prefix') || '') +
              inputValue +
              ($output.attr('data-field-suffix') || '');
            $output.text(text);

            // Set output top position.
            let top;
            if (display === 'above') {
              top = $input.position().top - $output.outerHeight() + 2;
            } else {
              top = $input.position().top + $input.outerHeight() + 2;
            }

            // It is impossible to accurately calculate the exact position of the
            // range's buttons so we only incrementally move the output bubble.
            const inputWidth = $input.outerWidth();
            const buttonPosition = Math.floor(
              (inputWidth * (inputValue - $input.attr('min'))) /
                ($input.attr('max') - $input.attr('min')),
            );
            const increment = Math.floor(inputWidth / 5);
            const outputWidth = $output.outerWidth();

            // Set output left position.
            let left;
            if (buttonPosition <= increment) {
              left = 0;
            } else if (buttonPosition <= increment * 2) {
              left = increment * 1.5 - outputWidth;
              if (left < 0) {
                left = 0;
              }
            } else if (buttonPosition <= increment * 3) {
              left = increment * 2.5 - outputWidth / 2;
            } else if (buttonPosition <= increment * 4) {
              left = increment * 4 - outputWidth;
              if (left > increment * 5 - outputWidth) {
                left = increment * 5 - outputWidth;
              }
            } else if (buttonPosition <= inputWidth) {
              left = increment * 5 - outputWidth;
            }
            // Also make sure to include the input's left position.
            left = Math.floor($input.position().left + left);

            // Finally, position the output.
            $output.css({ top, left });
          })
          // Fake a change to position output at page load.
          .trigger('input');

        // Add fade in/out event handlers if opacity is defined.
        const defaultOpacity = $output.css('opacity');
        if (defaultOpacity < 1) {
          // Fade in/out on focus/blur of the input.
          $input.on('focus mouseover', function () {
            $output.stop().fadeTo('slow', 1);
          });
          $input.on('blur mouseout', function () {
            $output.stop().fadeTo('slow', defaultOpacity);
          });
          // Also fade in when focusing the output.
          $output.on('touchstart mouseover', function () {
            $output.stop().fadeTo('slow', 1);
          });
        }
      });
    },
  };
})(jQuery, Drupal, once);
