<?php

namespace Drupal\search_research\Plugin\ChadoSearch;

use Drupal\trpcultivate_chadosearch\ChadoSearch\Interfaces\ChadoSearchInterface;
use Drupal\trpcultivate_chadosearch\ChadoSearch\ChadoSearchPluginBase;
use Drupal\Core\Database\Query\Select;

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

    $query = $this->chado_connection->select('1:project', 'study');
    $query->fields('study', ['project_id', 'name', 'description']);
    $query->condition('study.type_id', $this->getType('SIO', '001066'), 'IN');

    // Add Genus to the query.
    $query->addJoin(
      'LEFT',
      '1:projectprop',
      'genus',
      'genus.project_id=study.project_id',
    );
    $query->condition('genus.type_id', $this->getType('TAXRANK', '0000005'), 'IN');
    $query->addField('genus', 'value', 'genus');

    // Add Year to the query.
    $query->addJoin(
      'LEFT',
      '1:projectprop',
      'year',
      'year.project_id=study.project_id',
    );
    $query->condition('year.type_id', $this->getType('NCIT', 'C29848'), 'IN');
    $query->addField('year', 'value', 'year');

    // -- Genus.
    if ($filter_results['genus'] != '') {
      $query->condition('genus.value', $filter_results['genus'], '~*');
    }

    // -- Name.
    if ($filter_results['name'] != '') {
      $query->condition('study.name', $filter_results['name'], '~*');
    }

    // Sort even though it is SLOW b/c ppl expect it.
    $query->addExpression('substring(year.value, 6, 9)', 'shortened_year');
    $query->orderBy('shortened_year', 'DESC');
    $query->orderBy('study.name', 'ASC');

    // Add the offset to the query for paging.
    if ($offset and is_numeric($offset)) {
      $query->range($offset, 26);
    }
    else {
      $query->range(0, 26);
    }

  }

  /**
   * Retrieve the cvterm ID for the given accession.
   *
   * @param string $idSpace
   *   The ID Space of the term you want to retrieve the cvterm_id for.
   * @param string $accession
   *   The accession of the term you want to retrieve the cvterm_id for.
   *
   * @return int
   *   The cvterm_id of the term we want to retrieve.
   */
  public function getType(string $idSpace, string $accession): int {
    $query = $this->chado_connection->select('1:cvterm', 'cvt')
      ->fields('cvt', ['cvterm_id']);

    $query->addJoin(
      'LEFT',
      '1:dbxref',
      'dbx',
      'dbx.dbxref_id=cvt.dbxref_id',
    );
    $query->condition('dbx.accession', $accession);

    $query->addJoin(
      'LEFT',
      '1:db',
      'db',
      'db.db_id=dbx.db_id',
    );
    $query->condition('db.name', $idSpace);

    $result = $query->execute();
    return $result->fetchField();
  }

  /**
   * {@inheritdoc}
   *
   * @todo Add content type linking for the study titles.
   * @todo Add a read more link at the end of a shortened description.
   * @todo Add styling to the results.
   * @todo Update the elements to use nested render arrays rather then
   *   concatenating HTML strings together.
   */
  public function formatResults(array &$form, array $results): void {
    $list = [];

    foreach ($results as $r) {
      $title = $r->name;

      // Substring the description.
      $description = strip_tags($r->description);
      if (preg_match('/^.{1,300}\b/s', $description, $match)) {
        $description = trim($match[0]);
      }

      $markup = '
        <div class="result-row">
          <div class="result-left">
            <div class="result-title">' . $title . '</div>
            <div class="result-description">' . $description . '</div>
          </div>
	  <div class="result-right">';
      if (isset($r->year)) {
        $markup .= '<div class="result-year" title="Year(s) of Funding">' . $r->year . '</div>';
      }
      if (isset($r->genus)) {
        $markup .= '<div class="result-genus" title="Genus for germplasm">' . $r->genus . '</div>';
      }
      if (isset($r->category)) {
        $markup .= '<div class="result-category" title="Research Category">' . $r->category . '</div>';
      }
      $markup .= '
          </div>
        </div>';
      $list[] = [
        '#type' => 'markup',
        '#markup' => $markup,
      ];
    }

    $form['results'] = [
      '#theme' => 'item_list',
      '#items' => $list,
      '#type' => 'ul',
      '#weight' => 50,
    ];
  }

}
