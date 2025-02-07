ARG drupalversion=11.1.x-dev
ARG phpversion=8.3
ARG pgsqlversion=16
FROM knowpulse/tripalcultivate-base:drupal${drupalversion}-php${phpversion}-pgsql${pgsqlversion}

COPY . /var/www/drupal/web/modules/contrib/TripalCultivate-ChadoSearch
WORKDIR /var/www/drupal/web/modules/contrib/TripalCultivate-ChadoSearch

RUN service postgresql start \
  && drush en trpcultivate_chadosearch search_research --yes \
  && drush tripal:trp-run-jobs --username=drupaladmin \
  && drush cr \
  && service postgresql stop
