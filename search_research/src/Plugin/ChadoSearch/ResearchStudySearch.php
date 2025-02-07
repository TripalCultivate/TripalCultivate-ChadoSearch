<?php

namespace Drupal\search_research\Plugin\ChadoSearch;

use Drupal\trpcultivate_chadosearch\ChadoSearch\Interfaces\ChadoSearchInterface;
use Drupal\trpcultivate_chadosearch\ChadoSearch\ChadoSearchPluginBase;

/**
 * Creates a search for research studies and their associated experiments.
 *
 *  @ChadoSearch(
 *    id = "research_study",
 *    title = @Translation("Research Studies"),
 *    description = @Translation("Explore ongoing and past research studies and their associated experiments."),
 *    permissions = {"access content"},
 *    url_path = "research-study/search",
 *    button_text = @Translation("Search"),
 *    require_submit = FALSE,
 *    pager = TRUE,
 *    num_items_per_page = 25,
 *  )
 */
class ResearchStudySearch extends ChadoSearchPluginBase implements ChadoSearchInterface {

}
