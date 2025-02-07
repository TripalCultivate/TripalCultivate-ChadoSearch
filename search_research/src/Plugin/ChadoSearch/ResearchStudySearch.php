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

  /**
   * Information regarding the fields and filters for this search.
   *
   * @var array
   */
  public static array $info = [
    // Lists the columns in your results table.
    'fields' => [
      'name' => [
        'title' => 'Name',
        'entity_link' => [
          'chado_table' => 'project',
          'id_column' => 'project_id',
        ],
      ],
      'genus' => [
        'title' => 'Genus',
      ],
      'year' => [
        'title' => 'Dates',
      ],
      'category' => [
        'title' => 'Category',
      ],
    ],
    // The filter criteria available to the user.
    // This is used to generate a search form which can be altered.
    'filters' => [
      'genus' => [
        'title' => 'Genus',
        'help' => 'The legume species the research study is focused on (e.g. Lens culinaris).',
        'default' => 'Lens',
      ],
      'species' => [
        'title' => 'Species',
        'help' => '',
      ],
      'name' => [
        'title' => 'Name',
        'help' => 'The name of the research study you are interested in (partial names are accepted).',
      ],
    ],
  ];

  /**
   * Determine the query for the research study search.
   *
   * @param Drupal\Core\Database\Query\Select|null $query
   *   A Drupal select query object used to retrieve the results.
   *   This parameter will be NULL when requested and initialized inside
   *   this method.
   * @param int $offset
   *   The number of records to offset for the results. This is used in paging.
   */
  public function getQuery(Select|null &$query, int $offset = 0) {

    // Retrieve the filter results already set.
    $filter_results = $this->values;

    // @todo Collaborators: 5311
    $query = "
      SELECT
        exp.name, exp.project_id,
        exp.description,
        genus.value as genus,
        yr.value as year,
        cat.value as category
      FROM {project} exp
      LEFT JOIN {projectprop} genus ON genus.project_id=exp.project_id
        AND genus.type_id=4032 AND genus.rank=0
      LEFT JOIN {projectprop} yr ON yr.project_id=exp.project_id
        AND yr.type_id=6364 AND yr.rank=0
      LEFT JOIN {projectprop} cat ON cat.project_id=exp.project_id
        AND cat.type_id=6365 AND cat.rank=0
      LEFT JOIN {projectprop} re ON re.project_id=exp.project_id
        AND re.type_id=4310 AND re.rank=0";

    $where = [];
    $joins = [];

    $where[] = "re.value = :content_type";
    $args[':content_type'] = 'SIO:Study';
    // -- Genus.
    if ($filter_results['genus'] != '') {
      $where[] = "genus.value ~* :genus";
      $args[':genus'] = $filter_results['genus'];
    }

    // -- Species.
    if ($filter_results['species'] != '') {
      $where[] = "species.value ~* :species";
      $args[':species'] = $filter_results['species'];
    }

    // -- Name.
    if ($filter_results['name'] != '') {
      $where[] = "exp.name ~* :name";
      $args[':name'] = $filter_results['name'];
    }

    // Finally, add it to the query.
    if (!empty($joins)) {
      $query .= implode("\n", $joins);
    }
    if (!empty($where)) {
      $query .= "\n" . ' WHERE ' . implode(' AND ', $where);
    }

    // Sort even though it is SLOW b/c ppl expect it.
    $query .= "\n" . ' ORDER BY substring(yr.value, 6, 9) DESC, exp.name ASC';

    // Handle paging.
    $limit = $this::$num_items_per_page + 1;
    $query .= "\n" . ' LIMIT ' . $limit . ' OFFSET ' . $offset;

    // @debug dpm(strtr(str_replace(['{','}'], ['chado.', ''], $query), $args), 'query');
  }

  /**
   * {@inheritdoc}
   */
  public function formatResults(array &$form, array $results): void {
    $list = [];

    foreach ($results as $r) {
      // Add a link to the title.
      $title = $r->name;
      $id = NULL;
      if ($r->project_id) {
        $id = chado_get_record_entity_by_table('project', $r->project_id);
        if ($id) {
          $title = l($r->name, 'bio_data/' . $id);
        }
      }

      // Substring the description.
      $description = strip_tags($r->description);
      if (preg_match('/^.{1,300}\b/s', $description, $match)) {
        $description = trim($match[0]);
      }
      if ($id && (strlen($description) > 0)) {
        $description .= ' ' . l('[Read more]', 'bio_data/' . $id);
      }

      $markup = '
        <div class="result-row">
          <div class="result-left">
            <div class="result-title">' . $title . '</div>
            <div class="result-description">' . $description . '</div>
          </div>
	  <div class="result-right">';
      if ($r->year) {
        $markup .= '<div class="result-year" title="Year(s) of Funding">' . $r->year . '</div>';
      }
      if ($r->genus) {
        $markup .= '<div class="result-genus" title="Genus for germplasm">' . $r->genus . '</div>';
      }
      if ($r->category) {
        $markup .= '<div class="result-category" title="Research Category">' . $r->category . '</div>';
      }
      $markup .= '
          </div>
        </div>';
      $list[] = $markup;
    }

    $form['results'] = [
      '#theme' => 'item_list',
      '#items' => $list,
      '#type' => 'ul',
      '#weight' => 50,
    ];
  }

}
