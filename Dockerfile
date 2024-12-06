ARG drupalversion=11.1.x-dev
ARG phpversion=8.3
ARG pgsqlversion=16
FROM knowpulse/tripalcultivate-tripal:drupal${drupalversion}-php${phpversion}-pgsql${pgsqlversion}

COPY . /var/www/drupal/web/modules/contrib/chado_search
WORKDIR /var/www/drupal/web/modules/contrib/chado_search

RUN service postgresql start \
  && drush en chado_search --yes \
  && drush tripal:trp-run-jobs --username=drupaladmin \
  && drush cr \
  && service postgresql stop
