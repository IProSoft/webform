<?php

namespace Drupal\webform_test_prepopulate_block\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Provides a custom block for Webform prepopulate entity testing.
 */
#[Block(
    id: "webform_test_prepopulate_block",
    admin_label: new TranslatableMarkup("Weboform prepopulate block"),
    category: new TranslatableMarkup("Webform test")
    )]
    class WebformPrepopulateBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#markup' => '<p><a class="webform-dialog webform-dialog-wide button" href="/webform/test_form_prepopulate?source_entity_type=ENTITY_TYPE&source_entity_id=ENTITY_ID">Prepopulate test form</a></p>',
    ];
  }

    }
