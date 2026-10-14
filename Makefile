# ---------------------------------------------------------------------------
# Challenge PHP Hexagonal - GIFs API
#
# Shortcuts for the usual tasks. Run `make help` to list them.
# ---------------------------------------------------------------------------
DC := docker compose
APP := $(DC) exec -T app

.DEFAULT_GOAL := help
.PHONY: help env up build down restart logs shell test test-unit test-feature \
        migrate fresh seed keys clients pint routes

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-14s\033[0m %s\n", $$1, $$2}'

env: ## Create .env from .env.example when missing
	@test -f .env || (cp .env.example .env && echo "Created .env - remember to set GIPHY_API_KEY")

up: env ## Build (if needed) and start the stack
	$(DC) up -d --build
	@echo "API ready at http://localhost:$${APP_PORT:-8080}/api/v1"

build: env ## Rebuild the images
	$(DC) build --no-cache

down: ## Stop the stack
	$(DC) down

restart: ## Restart the stack
	$(DC) restart

logs: ## Follow the logs
	$(DC) logs -f --tail=100

shell: ## Open a shell inside the app container
	$(DC) exec app bash

test: ## Run the whole test suite
	$(APP) php artisan test

test-unit: ## Run the unit + architecture tests only
	$(APP) php artisan test --testsuite=Unit,Architecture

test-feature: ## Run the feature tests only
	$(APP) php artisan test --testsuite=Feature

migrate: ## Run pending migrations
	$(APP) php artisan migrate --force

fresh: ## Drop everything, migrate and seed again
	$(APP) php artisan migrate:fresh --force
	$(APP) php artisan oauth:ensure-clients
	$(APP) php artisan db:seed --force

seed: ## Seed the demo user
	$(APP) php artisan db:seed --force

keys: ## Generate the OAuth2 signing keys
	$(APP) php artisan passport:keys --force

clients: ## Create the OAuth2 clients if they do not exist
	$(APP) php artisan oauth:ensure-clients

pint: ## Fix the code style
	$(APP) ./vendor/bin/pint

routes: ## List the API routes
	$(APP) php artisan route:list --path=api
