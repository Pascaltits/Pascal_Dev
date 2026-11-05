# Environnement de développement de plugins Dotclear
# `make help` liste les commandes.

COMPOSE ?= docker compose
PHP     ?= php
PLUGINS_DIR := plugins
-include .env
export

.DEFAULT_GOAL := help

help: ## Affiche cette aide
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-16s\033[0m %s\n", $$1, $$2}'

## --- Conteneurs -------------------------------------------------------------

up: ## Construit et démarre Dotclear + MariaDB + Adminer
	@[ -f .env ] || cp .env.example .env
	$(COMPOSE) up -d --build
	@echo "Blog : $${DC_PUBLIC_URL:-http://localhost:8080}/   Admin : $${DC_PUBLIC_URL:-http://localhost:8080}/admin/"

down: ## Arrête les conteneurs (les données sont conservées)
	$(COMPOSE) down

restart: ## Redémarre le conteneur Dotclear (recharge php.ini / xdebug.ini)
	$(COMPOSE) restart dotclear

logs: ## Suit les logs Apache/PHP (erreurs PHP, Xdebug)
	$(COMPOSE) logs -f dotclear

shell: ## Ouvre un shell dans le conteneur Dotclear
	$(COMPOSE) exec dotclear bash

db-shell: ## Ouvre un client MariaDB sur la base Dotclear
	$(COMPOSE) exec db mariadb -u$${DC_DBUSER:-dotclear} -p$${DC_DBPASSWORD:-dotclear} $${DC_DBNAME:-dotclear}

install: ## Crée le compte administrateur et le blog (après `make up`)
	$(COMPOSE) exec dotclear bash /workspace/scripts/dc-install.sh

reset: ## Supprime la base de données et la configuration pour repartir de zéro (demande confirmation)
	@read -p "Supprimer la base de données et dotclear/inc/config.php ? [y/N] " r && [ "$$r" = "y" ]
	$(COMPOSE) down -v
	rm -f dotclear/inc/config.php
	rm -rf dotclear/cache/* dotclear/var/*

core-update: ## Re-télécharge le cœur Dotclear (version DC_VERSION du .env)
	rm -f dotclear/index.php
	$(COMPOSE) up -d --force-recreate dotclear

## --- Qualité ----------------------------------------------------------------

vendor: composer.json ## Installe phpstan et php-cs-fixer
	composer install --no-interaction --prefer-dist

lint: ## Vérifie la syntaxe PHP des plugins
	@find $(PLUGINS_DIR) -name '*.php' -print0 | xargs -0 -n1 -P4 $(PHP) -l | grep -v '^No syntax errors' || true
	@echo "lint OK"

cs: vendor ## Vérifie le style de code (php-cs-fixer, sans modifier)
	vendor/bin/php-cs-fixer fix --dry-run --diff

cs-fix: vendor ## Corrige automatiquement le style de code
	vendor/bin/php-cs-fixer fix

stan: vendor dotclear/index.php ## Analyse statique (phpstan) des plugins contre l'API Dotclear
	vendor/bin/phpstan analyse --memory-limit=-1

check: lint cs stan ## Lance toutes les vérifications

dotclear/index.php:
	bash scripts/fetch-dotclear.sh

## --- Plugins ----------------------------------------------------------------

new-plugin: ## Crée un plugin à partir du modèle : make new-plugin ID=monPlugin NAME="Mon plugin"
	bash scripts/new-plugin.sh "$(ID)" "$(NAME)"

package: ## Construit l'archive zip d'un plugin : make package ID=monPlugin
	bash scripts/package-plugin.sh "$(ID)"

.PHONY: help up down restart logs shell db-shell install reset core-update vendor lint cs cs-fix stan check new-plugin package
