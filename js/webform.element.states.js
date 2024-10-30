/**
 * @file
 * JavaScript behaviors for element #states.
 */

(function ($, Drupal, drupalSettings, once) {
  /**
   * Element #states builder.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformElementStates = {
    attach(context) {
      $(
        once(
          'webform-element-states-condition',
          '.webform-states-table--condition',
          context,
        ),
      ).each(function () {
        const $condition = $(this);
        const $selector = $condition.find(
          '.webform-states-table--selector select',
        );
        const $value = $condition.find('.webform-states-table--value input');
        const $trigger = $condition.find(
          '.webform-states-table--trigger select',
        );

        // Initialize autocompletion.
        $value.autocomplete({ minLength: 0 }).on('focus', function () {
          $value.autocomplete('search', '');
        });

        // Initialize trigger and selector.
        $trigger.on('change', function () {
          $selector.trigger('change');
        });

        $selector
          .on('change', function () {
            const selector = $selector.val();
            const sourceKey =
              drupalSettings.webformElementStates.selectors[selector];
            const source =
              drupalSettings.webformElementStates.sources[sourceKey];
            const notPattern = $trigger.val().indexOf('pattern') === -1;
            if (source && notPattern) {
              // Enable autocompletion.
              $value
                .autocomplete('option', 'source', source)
                .addClass('form-autocomplete');
            } else {
              // Disable autocompletion.
              $value
                .autocomplete('option', 'source', [])
                .removeClass('form-autocomplete');
            }
            // Always disable browser auto completion.
            const off = /chrom(e|ium)/.test(
              window.navigator.userAgent.toLowerCase(),
            )
              ? `chrome-off-${Math.floor(Math.random() * 100000000)}`
              : 'off';
            $value.attr('autocomplete', off);
          })
          .trigger('change');
      });

      // If the states:state is required or optional the required checkbox
      // should be checked and disabled.
      const $state = $(context).find('.webform-states-table--state select');
      if ($state.length) {
        $(once('webform-element-states-state', $state)).on(
          'change',
          toggleRequiredCheckbox,
        );
        toggleRequiredCheckbox();
      }
    },
  };

  /**
   * Track required checked state.
   *
   * @type {null|boolean}
   */
  let requiredChecked = null;

  /**
   * Toggle the required checkbox when states:state is required or optional.
   */
  function toggleRequiredCheckbox() {
    const $input = $('input[name="properties[required]"]');
    if (!$input.length) {
      return;
    }

    // Determine if any states:state is required or optional.
    let required = false;
    $('.webform-states-table--state select').each(function () {
      const value = $(this).val();
      if (value === 'required' || value === 'optional') {
        required = true;
      }
    });

    if (required) {
      requiredChecked = $input.prop('checked');
      $input.attr('disabled', true);
      $input.prop('checked', true);
    } else {
      $input.attr('disabled', false);
      if (requiredChecked !== null) {
        $input.prop('checked', requiredChecked);
        requiredChecked = null;
      }
    }
    $input.trigger('change');
  }
})(jQuery, Drupal, drupalSettings, once);
