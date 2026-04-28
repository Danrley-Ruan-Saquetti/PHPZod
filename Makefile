down:
	@docker compose down -v
.PHONY: down

deps:
	@docker compose --profile deps run --rm composer install
.PHONY: deps

cli:
	@docker compose --profile cli run --rm validator bash
.PHONY: cli

test:
	@docker compose --profile test run --rm composer run test
.PHONY: test
