COMPOSER = ./composer.phar
COMPOSER_CONF = ./composer.json

.PHONY: all vendor

all: vendor

$(COMPOSER):
	curl -sS https://getcomposer.org/installer | php

vendor: $(COMPOSER) $(COMPOSER_CONF)
	$(COMPOSER) self-update
	$(COMPOSER) install --no-interaction --optimize-autoloader --prefer-dist

deploy:
	rm deploy.zip
	mkdir -p esi-calendar-to-ics
	cp index.php esi-calendar-to-ics/index.php
	cp -r vendor esi-calendar-to-ics/vendor
	zip -r deploy.zip esi-calendar-to-ics
	rm -rf esi-calendar-to-ics
