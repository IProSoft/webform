/**
 * @file
 * JavaScript behaviors for CodeMirror integration.
 */

(function ($, Drupal, once) {
  // @see http://codemirror.net/doc/manual.html#config
  Drupal.webform = Drupal.webform || {};
  Drupal.webform.codeMirror = Drupal.webform.codeMirror || {};
  Drupal.webform.codeMirror.options = Drupal.webform.codeMirror.options || {};

  /**
   * Initialize CodeMirror editor.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformCodeMirror = {
    attach(context) {
      if (!window.CodeMirror) {
        return;
      }

      // Webform CodeMirror editor.
      $(
        once('webform-codemirror', 'textarea.js-webform-codemirror', context),
      ).each(function () {
        const $input = $(this);

        // Open all closed details, so that editor height is correctly calculated.
        const $details = $input.parents('details:not([open])');
        $details.attr('open', 'open');

        // #59 HTML5 required attribute breaks hack for webform submission.
        // https://github.com/marijnh/CodeMirror-old/issues/59
        $input.removeAttr('required');

        const options = $.extend(
          {
            mode: $input.attr('data-webform-codemirror-mode'),
            lineNumbers: true,
            lineWrapping: $input.attr('wrap') !== 'off',
            viewportMargin: Infinity,
            readOnly: !!($input.prop('readonly') || $input.prop('disabled')),
            extraKeys: {
              // Setting for using spaces instead of tabs - https://github.com/codemirror/CodeMirror/issues/988
              Tab(cm) {
                const spaces = Array(cm.getOption('indentUnit') + 1).join(' ');
                cm.replaceSelection(spaces, 'end', '+element');
              },
              // On 'Escape' move to the next tabbable input.
              // @see http://bgrins.github.io/codemirror-accessible/
              Esc(cm) {
                // Must show and then textarea so that we can determine
                // its tabindex.
                const textarea = $(cm.getTextArea());
                $(textarea).show().addClass('visually-hidden');
                const $tabbable = $(':tabbable');
                const tabindex = $tabbable.index(textarea);
                $(textarea).hide().removeClass('visually-hidden');

                // Tabindex + 2 accounts for the CodeMirror's iframe.
                $tabbable.eq(tabindex + 2).trigger('focus');
              },
            },
          },
          Drupal.webform.codeMirror.options,
        );

        const editor = CodeMirror.fromTextArea(this, options);

        // Now, close details.
        $details.removeAttr('open');

        // Apply the textarea's min/max-height to the CodeMirror editor.
        if ($input.css('min-height')) {
          const minHeight = $input.css('min-height');
          $(editor.getWrapperElement())
            .css('min-height', minHeight)
            .find('.CodeMirror-scroll')
            .css('min-height', minHeight);
        }
        if ($input.css('max-height')) {
          const maxHeight = $input.css('max-height');
          $(editor.getWrapperElement())
            .css('max-height', maxHeight)
            .find('.CodeMirror-scroll')
            .css('max-height', maxHeight);
        }

        // Issue #2764443: CodeMirror is not setting submitted value when
        // rendered within a webform UI dialog or within an Ajaxified element.
        let changeTimer = null;
        editor.on('change', function () {
          if (changeTimer) {
            window.clearTimeout(changeTimer);
            changeTimer = null;
          }
          changeTimer = setTimeout(function () {
            editor.save();
          }, 500);
        });

        // Update CodeMirror when the textarea's value has changed.
        // @see webform.states.js
        $input.on('change', function () {
          editor.getDoc().setValue($input.val());
        });

        // Set CodeMirror to be readonly when the textarea is disabled.
        // @see webform.states.js
        $input.on('webform:disabled', function () {
          editor.setOption('readOnly', $input.is(':disabled'));
        });

        // Delay refreshing CodeMirror for 500 millisecond while the dialog is
        // still being rendered.
        // @see http://stackoverflow.com/questions/8349571/codemirror-editor-is-not-loading-content-until-clicked
        setTimeout(function () {
          // Show tab panel and open details.
          const $tabPanel = $input.parents('.ui-tabs-panel:hidden');
          const $details = $input.parents('details:not([open])');

          if (!$tabPanel.length && $details.length) {
            return;
          }

          $tabPanel.show();
          $details.attr('open', 'open');

          editor.refresh();

          // Hide tab panel and close details.
          $tabPanel.hide();
          $details.removeAttr('open');
        }, 500);
      });

      // Webform CodeMirror syntax coloring.
      if (window.CodeMirror.runMode) {
        $(
          once(
            'webform-codemirror-runmode',
            '.js-webform-codemirror-runmode',
            context,
          ),
        ).each(function () {
          // Mode Runner - http://codemirror.net/demo/runmode.html
          CodeMirror.runMode(
            $(this).addClass('cm-s-default').text(),
            $(this).attr('data-webform-codemirror-mode'),
            this,
          );
        });
      }
    },
  };
})(jQuery, Drupal, once);
