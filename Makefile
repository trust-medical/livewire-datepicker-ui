# Development helper commands. Everything runs inside Docker, so the host only
# needs Docker + Docker Compose installed.
export UID := $(shell id -u)
export GID := $(shell id -g)

DC   := docker compose
PHP  := $(DC) run --rm php
NODE := $(DC) run --rm node

.DEFAULT_GOAL := help

.PHONY: help build-images install install-php install-js \
        test test-php test-js e2e screenshots build \
        lint lint-php lint-js typecheck format ci \
        serve shell-php shell-node down clean

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-18s\033[0m %s\n", $$1, $$2}'

build-images: ## Build the php and node Docker images
	$(DC) build

install: install-php install-js ## Install all dependencies

install-php: ## Install Composer dependencies
	$(PHP) composer install

install-js: ## Install pnpm dependencies
	$(NODE) pnpm install

test: test-php test-js ## Run the PHP + JS unit/feature suites

test-php: ## Run Pest (Unit + Feature)
	$(PHP) composer test

test-js: ## Run Vitest
	$(NODE) pnpm test

e2e: ## Run Playwright E2E (requires `make serve` running in another shell)
	$(NODE) pnpm test:e2e

screenshots: ## Capture README screenshots (requires `make serve` running in another shell)
	$(NODE) sh -lc 'pnpm build && pnpm workbench:css && pnpm screenshots'

build: ## Build the JS library into dist/
	$(NODE) pnpm build

lint: lint-php lint-js ## Run all linters

lint-php: ## Check PHP code style with Pint
	$(PHP) composer lint

lint-js: ## Lint TS with ESLint + check Prettier formatting
	$(NODE) pnpm lint
	$(NODE) pnpm format:check

typecheck: ## Type-check TS (tsc) and analyse PHP (Larastan)
	$(NODE) pnpm typecheck
	$(PHP) composer analyse

format: ## Auto-format PHP (Pint) and TS/CSS (Prettier)
	$(PHP) composer format
	$(NODE) pnpm format

ci: lint typecheck test build ## Run the full local CI pipeline

serve: ## Start the Testbench workbench server at http://localhost:8000 (for `make e2e`)
	# --service-ports publishes 8000 on the host (browser access); --use-aliases
	# attaches the `php` network alias so the node container can reach this server
	# at http://php:8000 during `make e2e`.
	$(DC) run --rm --service-ports --use-aliases php composer run serve -- --host=0.0.0.0 --port=8000

shell-php: ## Open a shell in the php container
	$(PHP) sh

shell-node: ## Open a shell in the node container
	$(NODE) bash

down: ## Stop and remove containers
	$(DC) down --remove-orphans

clean: ## Remove all build artifacts and dependencies
	rm -rf vendor node_modules dist coverage build playwright-report test-results
