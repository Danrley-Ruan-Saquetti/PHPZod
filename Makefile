ARGS ?=

down:
	@docker compose down -v $(ARGS)
.PHONY: down

deps:
	@docker compose --profile deps run --rm composer install $(ARGS)
.PHONY: deps

cli:
	@docker compose --profile cli run --rm validator bash $(ARGS)
.PHONY: cli

test:
	@docker compose --profile test run --rm composer run test $(ARGS)
.PHONY: test
